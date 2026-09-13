<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Generate QR Code untuk verifikasi dokumen PDF.
 * Output SVG data URI supaya bisa disematkan langsung di HTML PDF (DomPDF friendly).
 */
class QrCodeService
{
    /**
     * Generate QR sebagai SVG data URI. DomPDF support inline SVG via <img src="data:image/svg+xml;base64,...">.
     */
    public function svgDataUri(string $content, int $size = 120): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd(),
        );

        $svg = (new Writer($renderer))->writeString($content);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Build URL verifikasi untuk dokumen. Format:
     *   {verify_base}/verify/{docType}/{docId}
     *
     * Docs yang di-support: kuitansi, resep, resume, hasil-lab.
     */
    public function verifyUrl(string $docType, string $docId): string
    {
        $base = rtrim(config('sihrs.verify_base_url'), '/');

        return "{$base}/verify/{$docType}/{$docId}";
    }
}
