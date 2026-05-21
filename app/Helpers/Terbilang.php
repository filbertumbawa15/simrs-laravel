<?php

namespace App\Helpers;

class Terbilang
{
    public static function convert(float|int $angka): string
    {
        $angka = abs((int) $angka);
        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) return $bilangan[$angka];
        if ($angka < 20) return self::convert($angka - 10).' belas';
        if ($angka < 100) return self::convert((int)($angka / 10)).' puluh '.self::convert($angka % 10);
        if ($angka < 200) return 'seratus '.self::convert($angka - 100);
        if ($angka < 1000) return self::convert((int)($angka / 100)).' ratus '.self::convert($angka % 100);
        if ($angka < 2000) return 'seribu '.self::convert($angka - 1000);
        if ($angka < 1_000_000) return self::convert((int)($angka / 1000)).' ribu '.self::convert($angka % 1000);
        if ($angka < 1_000_000_000) return self::convert((int)($angka / 1_000_000)).' juta '.self::convert($angka % 1_000_000);
        if ($angka < 1_000_000_000_000) return self::convert((int)($angka / 1_000_000_000)).' miliar '.self::convert($angka % 1_000_000_000);
        return 'nol';
    }
}
