<?php

namespace App\Enums;

enum AlasanRujuk: string
{
    case FasilitasTidakTersedia = 'FASILITAS_TIDAK_TERSEDIA';
    case SpesialisTidakTersedia = 'SPESIALIS_TIDAK_TERSEDIA';
    case KamarPenuh = 'KAMAR_PENUH';
    case IcuPenuh = 'ICU_PENUH';
    case PermintaanPasien = 'PERMINTAAN_PASIEN';
    case Lainnya = 'LAINNYA';

    public function label(): string
    {
        return match ($this) {
            self::FasilitasTidakTersedia => 'Fasilitas/Alat tidak tersedia',
            self::SpesialisTidakTersedia => 'Dokter spesialis tidak tersedia',
            self::KamarPenuh => 'Kamar rawat inap penuh',
            self::IcuPenuh => 'ICU/HCU penuh',
            self::PermintaanPasien => 'Permintaan pasien/keluarga',
            self::Lainnya => 'Lainnya',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
