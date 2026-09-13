<?php

namespace App\Mail;

use App\Models\OrderRadiologi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Email peringatan temuan kritis radiologi (mis. pneumothorax, perdarahan)
 * ke dokter perujuk. Wajib segera.
 */
class HasilRadiologiKritisMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrderRadiologi $order,
        public Collection $hasilKritis,
    ) {
    }

    public function envelope(): Envelope
    {
        $pasien = $this->order->kunjungan->pasien->nama;

        return new Envelope(
            subject: "🚨 [KRITIS] Temuan Radiologi - {$pasien} (RM {$this->order->kunjungan->pasien->no_rm})",
            tags: ['critical', 'radiologi'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.hasil-radiologi-kritis',
            with: [
                'order' => $this->order,
                'hasilKritis' => $this->hasilKritis,
                'pasien' => $this->order->kunjungan->pasien,
                'dokter' => $this->order->dokter,
                'rsNama' => config('sihrs.rs_nama', config('app.name')),
            ],
        );
    }
}
