<?php

namespace App\Enums;

enum StatusOrderRadiologi: string
{
    case Diorder = 'DIORDER';
    case Dilakukan = 'DILAKUKAN';
    case MenungguBacaan = 'MENUNGGU_BACAAN';
    case Selesai = 'SELESAI';
    case Batal = 'BATAL';

    public function label(): string
    {
        return match ($this) {
            self::Diorder => 'Diorder',
            self::Dilakukan => 'Dilakukan',
            self::MenungguBacaan => 'Menunggu Bacaan',
            self::Selesai => 'Selesai',
            self::Batal => 'Batal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Diorder => 'yellow',
            self::Dilakukan => 'blue',
            self::MenungguBacaan => 'purple',
            self::Selesai => 'green',
            self::Batal => 'red',
        };
    }
}
