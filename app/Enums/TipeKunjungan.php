<?php

namespace App\Enums;

enum TipeKunjungan: string
{
    case RawatJalan = 'RJ';
    case RawatInap = 'RI';
    case IGD = 'IGD';

    public function label(): string
    {
        return match ($this) {
            self::RawatJalan => 'Rawat Jalan',
            self::RawatInap => 'Rawat Inap',
            self::IGD => 'Instalasi Gawat Darurat',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::RawatJalan => 'bg-blue-100 text-blue-800',
            self::RawatInap => 'bg-purple-100 text-purple-800',
            self::IGD => 'bg-red-100 text-red-800',
        };
    }
}