<?php

namespace App\Enums;

enum StatusTagihan: string
{
    case Draft = 'DRAFT';
    case BelumLunas = 'BELUM_LUNAS';
    case Cicilan = 'CICILAN';
    case Lunas = 'LUNAS';
    case Klaim = 'KLAIM';
    case Void = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::BelumLunas => 'Belum Lunas',
            self::Cicilan => 'Cicilan',
            self::Lunas => 'Lunas',
            self::Klaim => 'Klaim Penjamin',
            self::Void => 'Void',
        };
    }
}
