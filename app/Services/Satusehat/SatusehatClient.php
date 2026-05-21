<?php

namespace App\Services\Satusehat;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Low-level client SATUSEHAT (FHIR R4).
 * Handle: OAuth2 token caching (1 jam), request/response logging.
 *
 * Docs: https://satusehat.kemkes.go.id/platform/docs/dokumentasi-teknis
 */
class SatusehatClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $authUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected string $organizationId,
    ) {}

    public static function make(): self
    {
        $cfg = config('services.satusehat');
        return new self(
            $cfg['base_url'],
            $cfg['auth_url'],
            $cfg['client_id'],
            $cfg['client_secret'],
            $cfg['organization_id'],
        );
    }

    public function organizationId(): string
    {
        return $this->organizationId;
    }

    /**
     * Get / refresh OAuth2 token. Cache 50 menit (token expire 1 jam).
     */
    public function token(): string
    {
        $cacheKey = "satusehat:token:{$this->clientId}";

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $response = Http::asForm()->post("{$this->authUrl}/accesstoken?grant_type=client_credentials", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (! $response->successful()) {
                throw new SatusehatException('Gagal auth SATUSEHAT', $response->status(), $response->json());
            }

            return $response->json('access_token');
        });
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, [], $query);
    }

    public function post(string $endpoint, array $body): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    public function put(string $endpoint, array $body): array
    {
        return $this->request('PUT', $endpoint, $body);
    }

    protected function request(string $method, string $endpoint, array $body = [], array $query = []): array
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');
        $token = $this->token();

        Log::channel('satusehat')->info('SATUSEHAT request', compact('method', 'url', 'body'));

        $http = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/fhir+json'])
            ->timeout(config('services.satusehat.timeout', 30));

        $response = match (strtoupper($method)) {
            'GET' => $http->get($url, $query),
            'POST' => $http->withBody(json_encode($body), 'application/fhir+json')->post($url),
            'PUT' => $http->withBody(json_encode($body), 'application/fhir+json')->put($url),
        };

        $responseBody = $response->json() ?? [];
        Log::channel('satusehat')->info('SATUSEHAT response', ['status' => $response->status(), 'body' => $responseBody]);

        if (! $response->successful()) {
            throw new SatusehatException(
                $this->extractError($responseBody),
                $response->status(),
                $responseBody,
            );
        }

        return $responseBody;
    }

    protected function extractError(array $body): string
    {
        // FHIR OperationOutcome
        if (($body['resourceType'] ?? '') === 'OperationOutcome') {
            return collect($body['issue'] ?? [])
                ->map(fn ($i) => $i['details']['text'] ?? $i['diagnostics'] ?? 'Unknown')
                ->implode('; ');
        }
        return $body['message'] ?? 'SATUSEHAT API error';
    }
}
