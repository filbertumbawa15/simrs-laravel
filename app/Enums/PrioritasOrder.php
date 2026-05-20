<?php

namespace App\Enums;

enum PrioritasOrder: string
{
    case Rutin = 'RUTIN';
    case Cito = 'CITO';

    public function label(): string
    {
        return match ($this) {
            self::Rutin => 'Rutin',
            self::Cito => 'CITO',
        };
    }

    public function targetMinutes(): int
    {
        return match ($this) {
            self::Rutin => 1440, // 24 jam
            self::Cito => 120,   // 2 jam
        };
    }
}
