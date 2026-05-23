<?php

namespace App\Http\Controllers;

use App\Models\Diagnosa;
use Illuminate\Http\Request;

class DiagnosaController extends Controller
{
    public function destroy(Diagnosa $diagnosa)
    {
        $kunjungan = $diagnosa->kunjungan;
        $diagnosa->delete();

        return back()->with('success', 'Diagnosa dihapus.');
    }
}
