<?php

namespace App\Exports;

use App\Models\Pasien;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export master pasien ke Excel.
 * Pakai FromQuery supaya hemat memory untuk data ribuan pasien.
 */
class PasienExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected ?string $search = null,
    ) {
    }

    public function query()
    {
        return Pasien::query()->when($this->search, fn ($q) => $q->search($this->search));
    }

    public function headings(): array
    {
        return [
            'No. RM',
            'NIK',
            'Nama',
            'Jenis Kelamin',
            'Tgl Lahir',
            'Umur',
            'Alamat',
            'Kota',
            'Telp',
            'Email',
            'Terdaftar',
        ];
    }

    public function map($p): array
    {
        return [
            $p->no_rm,
            $p->nik,
            $p->nama,
            $p->jenis_kelamin?->label(),
            $p->tgl_lahir?->format('Y-m-d'),
            $p->umur,
            $p->alamat,
            $p->kabupaten,
            $p->telp,
            $p->email,
            $p->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']]],
        ];
    }
}
