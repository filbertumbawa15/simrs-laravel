<?php

namespace App\Http\Controllers;

use App\Models\OrderLab;
use App\Models\Pembayaran;
use App\Models\RawatInap;
use App\Models\Resep;
use App\Services\PdfService;

class PdfController extends Controller
{
    public function __construct(protected PdfService $pdf) {}

    public function resep(Resep $resep) { return $this->pdf->resep($resep); }
    public function kuitansi(Pembayaran $pembayaran) { return $this->pdf->kuitansi($pembayaran); }
    public function resumeMedis(RawatInap $ri) { return $this->pdf->resumeMedis($ri); }
    public function hasilLab(OrderLab $order) { return $this->pdf->hasilLab($order); }
}
