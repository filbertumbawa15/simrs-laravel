<?php

namespace App\Enums;

enum TransportasiRujuk: string
{
    case AmbulansRs = 'AMBULANS_RS';
    case AmbulansLain = 'AMBULANS_LAIN';
    case KendaraanPribadi = 'KENDARAAN_PRIBADI';

    public function label(): string
    {
        return match ($this) {
            self::AmbulansRs => 'Ambulans RS ini',
            self::AmbulansLain => 'Ambulans pihak ketiga',
            self::KendaraanPribadi => 'Kendaraan pribadi',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
