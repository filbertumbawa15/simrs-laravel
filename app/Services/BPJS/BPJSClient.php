<?php

namespace App\Services\BPJS;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Low-level BPJS V-Claim client.
 * Handle: signature HMAC-SHA256, timestamp, decrypt AES-256-CBC LZString response.
 *
 * Docs resmi: https://dvlp.bpjs-kesehatan.go.id/
 */
class BPJSClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $consId,
        protected string $secretKey,
        protected string $userKey,
    ) {}

    public static function vclaim(): self
    {
        $cfg = config('services.bpjs.vclaim');
        return new self($cfg['base_url'], $cfg['cons_id'], $cfg['secret_key'], $cfg['user_key']);
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $payload): array
    {
        return $this->request('POST', $endpoint, $payload);
    }

    public function put(string $endpoint, array $payload): array
    {
        return $this->request('PUT', $endpoint, $payload);
    }

    public function delete(string $endpoint, array $payload = []): array
    {
        return $this->request('DELETE', $endpoint, $payload);
    }

    protected function request(string $method, string $endpoint, array $payload = []): array
    {
        $ts = (string) (time() - strtotime('1970-01-01 00:00:00'));
        $signature = base64_encode(hash_hmac('sha256', "{$this->consId}&{$ts}", $this->secretKey, true));

        $http = $this->buildHttp($ts, $signature);

        $url = rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');

        Log::channel('bpjs')->info('BPJS request', compact('method', 'url', 'payload'));

        $response = match (strtoupper($method)) {
            'GET' => $http->get($url),
            'POST' => $http->post($url, $payload),
            'PUT' => $http->put($url, $payload),
            'DELETE' => $http->delete($url, $payload),
        };

        $body = $response->json();
        Log::channel('bpjs')->info('BPJS response', ['status' => $response->status(), 'body' => $body]);

        if (! $response->successful() || ($body['metaData']['code'] ?? '500') !== '200') {
            throw new BPJSException(
                $body['metaData']['message'] ?? 'BPJS API error',
                (int) ($body['metaData']['code'] ?? 500),
                $body,
            );
        }

        // Response BPJS V-Claim ter-enkripsi (AES-256-CBC + LZString)
        if (isset($body['response']) && is_string($body['response'])) {
            $body['response'] = $this->decryptResponse($body['response'], $ts);
        }

        return $body;
    }

    protected function buildHttp(string $timestamp, string $signature): PendingRequest
    {
        return Http::withHeaders([
            'X-cons-id' => $this->consId,
            'X-timestamp' => $timestamp,
            'X-signature' => $signature,
            'user_key' => $this->userKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->timeout(config('services.bpjs.vclaim.timeout', 30));
    }

    /**
     * Decrypt response BPJS: AES-256-CBC, key = sha256(consId+secretKey+timestamp),
     * lalu inflate LZString.
     */
    protected function decryptResponse(string $encryptedBase64, string $timestamp): mixed
    {
        $key = hex2bin(hash('sha256', $this->consId.$this->secretKey.$timestamp));
        $iv = substr($key, 0, 16);

        $decrypted = openssl_decrypt(
            base64_decode($encryptedBase64),
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
        );

        if ($decrypted === false) {
            throw new BPJSException('Gagal dekripsi response BPJS', 500);
        }

        // LZString decompress (butuh package nadar/lzstring)
        if (class_exists(\Nadar\LzString\LzString::class)) {
            $decompressed = \Nadar\LzString\LzString::decompressFromEncodedURIComponent($decrypted);
            return json_decode($decompressed ?: $decrypted, true) ?? $decrypted;
        }

        return json_decode($decrypted, true) ?? $decrypted;
    }
}
