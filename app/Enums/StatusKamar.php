<?php

namespace App\Enums;

enum StatusKamar: string
{
    case Tersedia = 'TERSEDIA';
    case Terisi = 'TERISI';
    case Reserved = 'RESERVED';
    case Maintenance = 'MAINTENANCE';
    case Kotor = 'KOTOR';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Terisi => 'Terisi',
            self::Reserved => 'Reserved',
            self::Maintenance => 'Maintenance',
            self::Kotor => 'Perlu Dibersihkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Tersedia => 'green',
            self::Terisi => 'red',
            self::Reserved => 'yellow',
            self::Maintenance => 'gray',
            self::Kotor => 'orange',
        };
    }
}