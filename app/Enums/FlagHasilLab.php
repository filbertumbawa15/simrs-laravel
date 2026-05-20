<?php

namespace App\Enums;

enum FlagHasilLab: string
{
    case Normal = 'N';
    case Low = 'L';
    case High = 'H';
    case CriticalLow = 'LL';
    case CriticalHigh = 'HH';
    case Abnormal = 'A';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Low => 'Rendah',
            self::High => 'Tinggi',
            self::CriticalLow => 'Sangat Rendah',
            self::CriticalHigh => 'Sangat Tinggi',
            self::Abnormal => 'Abnormal',
        };
    }

    public function isCritical(): bool
    {
        return in_array($this, [self::CriticalLow, self::CriticalHigh]);
    }

    public function color(): string
    {
        return match ($this) {
            self::Normal => 'green',
            self::Low, self::High, self::Abnormal => 'yellow',
            self::CriticalLow, self::CriticalHigh => 'red',
        };
    }
}
