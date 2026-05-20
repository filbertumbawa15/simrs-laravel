<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case Tunai = 'TUNAI';
    case Debit = 'DEBIT';
    case Kredit = 'KREDIT';
    case Transfer = 'TRANSFER';
    case QRIS = 'QRIS';
    case BPJS = 'BPJS';
    case Asuransi = 'ASURANSI';

    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Debit => 'Kartu Debit',
            self::Kredit => 'Kartu Kredit',
            self::Transfer => 'Transfer Bank',
            self::QRIS => 'QRIS',
            self::BPJS => 'Klaim BPJS',
            self::Asuransi => 'Klaim Asuransi',
        };
    }
}
