<?php

namespace App\Enums;

enum StatusKunjungan: string
{
    case Terdaftar = 'TERDAFTAR';
    case DalamPemeriksaan = 'DALAM_PEMERIKSAAN';
    case MenungguHasilLab = 'MENUNGGU_HASIL_LAB';
    case MenungguObat = 'MENUNGGU_OBAT';
    case MenungguPembayaran = 'MENUNGGU_PEMBAYARAN';
    case Selesai = 'SELESAI';
    case LanjutRI = 'LANJUT_RI';
    case Batal = 'BATAL';

    public function label(): string
    {
        return match ($this) {
            self::Terdaftar => 'Terdaftar',
            self::DalamPemeriksaan => 'Dalam Pemeriksaan',
            self::MenungguHasilLab => 'Menunggu Hasil Lab',
            self::MenungguObat => 'Menunggu Obat',
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::Selesai => 'Selesai',
            self::LanjutRI => 'Lanjut Rawat Inap',
            self::Batal => 'Batal',
        };
    }
}
