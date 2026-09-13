<?php

namespace App\Mail;

use App\Models\OrderLab;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Email peringatan hasil lab kritis (flag LL/HH) ke DPJP.
 * Wajib dikirim segera — patient safety issue.
 */
class HasilLabKritisMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrderLab $order,
        public Collection $hasilKritis,
    ) {
    }

    public function envelope(): Envelope
    {
        $pasien = $this->order->kunjungan->pasien->nama;

        return new Envelope(
            subject: "🚨 [KRITIS] Hasil Lab - {$pasien} (RM {$this->order->kunjungan->pasien->no_rm})",
            tags: ['critical', 'lab'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.hasil-lab-kritis',
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
