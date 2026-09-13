<?php

namespace App\Exports;

use App\Models\Tagihan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export rekap tagihan bulanan untuk keuangan/direksi.
 */
class RekapTagihanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected Carbon $dari,
        protected Carbon $sampai,
        protected ?string $status = null,
    ) {
    }

    public function query()
    {
        return Tagihan::query()
            ->with(['kunjungan.pasien', 'pembayaran'])
            ->whereBetween('tgl_tagihan', [$this->dari, $this->sampai])
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('tgl_tagihan');
    }

    public function headings(): array
    {
        return [
            'No. Tagihan',
            'Tanggal',
            'No. Kunjungan',
            'Pasien',
            'No. RM',
            'Penjamin',
            'Subtotal',
            'Diskon',
            'PPN',
            'Total',
            'Dibayar',
            'Sisa',
            'Status',
        ];
    }

    public function map($t): array
    {
        return [
            $t->no_tagihan,
            $t->tgl_tagihan->format('Y-m-d'),
            $t->kunjungan->no_kunjungan,
            $t->kunjungan->pasien->nama,
            $t->kunjungan->pasien->no_rm,
            $t->kunjungan->penjamin?->label(),
            (float) $t->subtotal,
            (float) $t->diskon,
            (float) $t->ppn,
            (float) $t->total,
            (float) $t->dibayar,
            (float) $t->sisa,
            $t->status?->label() ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Rekap ' . $this->dari->format('Y-m-d') . ' sd ' . $this->sampai->format('Y-m-d');
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']]],
        ];
    }
}
