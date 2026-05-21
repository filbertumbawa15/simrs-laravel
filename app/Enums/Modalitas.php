<?php

namespace App\Enums;

enum Modalitas: string
{
    case Xray = 'XRAY';
    case Usg = 'USG';
    case Ct = 'CT';
    case Mri = 'MRI';
    case Fluoroscopy = 'FLUOROSCOPY';
    case Mammografi = 'MAMMOGRAFI';

    public function label(): string
    {
        return match ($this) {
            self::Xray => 'X-Ray (Rontgen)',
            self::Usg => 'USG',
            self::Ct => 'CT-Scan',
            self::Mri => 'MRI',
            self::Fluoroscopy => 'Fluoroscopy',
            self::Mammografi => 'Mammografi',
        };
    }
}
