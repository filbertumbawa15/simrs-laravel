<?php

namespace App\Http\Controllers;

use App\Exports\PasienExport;
use App\Exports\RekapTagihanExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Endpoint export data ke Excel. Akses dibatasi via route middleware.
 */
class ExportController extends Controller
{
    public function pasien(Request $request): BinaryFileResponse
    {
        $filename = 'pasien_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new PasienExport(search: $request->input('q')),
            $filename,
        );
    }

    public function rekapTagihan(Request $request): BinaryFileResponse
    {
        $dari = $request->input('dari')
            ? Carbon::parse($request->input('dari'))->startOfDay()
            : now()->startOfMonth();
        $sampai = $request->input('sampai')
            ? Carbon::parse($request->input('sampai'))->endOfDay()
            : now()->endOfMonth();

        $filename = 'rekap_tagihan_' . $dari->format('Ymd') . '_' . $sampai->format('Ymd') . '.xlsx';

        return Excel::download(
            new RekapTagihanExport($dari, $sampai, $request->input('status')),
            $filename,
        );
    }
}
