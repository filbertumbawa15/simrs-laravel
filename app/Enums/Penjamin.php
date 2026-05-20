<?php

namespace App\Enums;

enum Penjamin: string
{
    case Umum = 'UMUM';
    case BPJS = 'BPJS';
    case Asuransi = 'ASURANSI';

    public function label(): string
    {
        return match ($this) {
            self::Umum => 'Pasien Umum',
            self::BPJS => 'BPJS Kesehatan',
            self::Asuransi => 'Asuransi Swasta',
        };
    }
}