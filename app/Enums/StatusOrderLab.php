<?php

namespace App\Enums;

enum StatusOrderLab: string
{
    case Diorder = 'DIORDER';
    case SampelDiambil = 'SAMPEL_DIAMBIL';
    case Diproses = 'DIPROSES';
    case Validasi = 'VALIDASI';
    case Selesai = 'SELESAI';
    case Batal = 'BATAL';

    public function label(): string
    {
        return match ($this) {
            self::Diorder => 'Diorder',
            self::SampelDiambil => 'Sampel Diambil',
            self::Diproses => 'Diproses',
            self::Validasi => 'Menunggu Validasi',
            self::Selesai => 'Selesai',
            self::Batal => 'Batal',
        };
    }
}
