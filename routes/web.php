<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IgdController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RadiologiController;
use App\Http\Controllers\RawatInapController;
use App\Http\Controllers\RawatJalanController;
use App\Http\Controllers\ResepController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Pasien ---
    Route::resource('pasien', PasienController::class);

    // --- Kunjungan ---
    Route::controller(KunjunganController::class)->prefix('kunjungan')->name('kunjungan.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{kunjungan}', 'show')->name('show');
        Route::post('{kunjungan}/batal', 'batal')->name('batal');
    });

    // --- Rawat Jalan ---
    Route::controller(RawatJalanController::class)->prefix('rj')->name('rj.')->group(function () {
        Route::get('antrian', 'antrian')->name('antrian');
        Route::get('icd/search', 'searchIcd')->name('icd.search');
        Route::get('{rj}', 'show')->name('show');
        Route::post('{rj}/panggil', 'panggil')->name('panggil');
        Route::get('{rj}/periksa', 'periksa')->name('periksa');
        Route::post('{rj}/soap', 'simpanSoap')->name('soap');
        Route::post('{rj}/selesai', 'selesai')->name('selesai');
    });

    // --- Resep / Farmasi ---
    Route::controller(ResepController::class)->prefix('resep')->name('resep.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('obat/search', 'searchObat')->name('obat.search');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{resep}', 'show')->name('show');
        Route::post('{resep}/verifikasi', 'verifikasi')->name('verifikasi');
        Route::post('{resep}/serahkan', 'serahkan')->name('serahkan');
    });

    // --- Billing ---
    Route::controller(BillingController::class)->prefix('billing')->name('billing.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('generate/{kunjungan}', 'generate')->name('generate');
        Route::get('{tagihan}', 'show')->name('show');
        Route::post('{tagihan}/finalize', 'finalize')->name('finalize');
        Route::get('{tagihan}/bayar', 'bayarForm')->name('bayar.form');
        Route::post('{tagihan}/bayar', 'bayar')->name('bayar');
    });

    Route::controller(LabController::class)->prefix('lab')->name('lab.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{order}', 'show')->name('show');
        Route::post('{order}/sampling', 'sampling')->name('sampling');
        Route::post('{order}/proses', 'proses')->name('proses');
        Route::get('{order}/input', 'inputForm')->name('input');
        Route::post('{order}/input', 'inputStore')->name('input.store');
        Route::post('{order}/validate', 'validate_')->name('validate');
    });

    Route::controller(RawatInapController::class)->prefix('ri')->name('ri.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('admisi', 'admisiForm')->name('admisi.form');
        Route::post('admisi', 'admisiStore')->name('admisi.store');
        Route::get('{ri}', 'show')->name('show');
        Route::get('{ri}/pindah', 'pindahForm')->name('pindah.form');
        Route::post('{ri}/pindah', 'pindahStore')->name('pindah.store');
        Route::get('{ri}/pulang', 'pulangForm')->name('pulang.form');
        Route::post('{ri}/pulang', 'pulangStore')->name('pulang.store');
    });

    Route::controller(KamarController::class)->prefix('kamar')->name('kamar.')->group(function () {
        Route::get('board', 'board')->name('board');
        Route::post('{kamar}/status', 'updateStatus')->name('update.status');
    });

    Route::controller(IgdController::class)->prefix('igd')->name('igd.')->group(function () {
        Route::get('/', 'board')->name('board');
        Route::get('{kunjungan}/triase', 'triaseForm')->name('triase.form');
        Route::post('{kunjungan}/triase', 'triaseStore')->name('triase.store');
        Route::get('/periksa/{kunjungan}', 'periksa')->name('periksa');
        Route::post('/periksa/{kunjungan}', 'periksaStore')->name('periksa.store');
        Route::post('/admisi/{kunjungan}', 'admisi')->name('admisi.store');
        Route::post('/rujuk/{kunjungan}', 'rujuk')->name('rujuk.store');
        Route::post('/pulang/{kunjungan}', 'pulangIgd')->name('pulang.store');
        Route::post('/meninggal/{kunjungan}', 'meninggal')->name('meninggal.store');
    });

    Route::controller(PdfController::class)->prefix('pdf')->name('pdf.')->group(function () {
        Route::get('resep/{resep}', 'resep')->name('resep');
        Route::get('kuitansi/{pembayaran}', 'kuitansi')->name('kuitansi');
        Route::get('resume-medis/{ri}', 'resumeMedis')->name('resume');
        Route::get('hasil-lab/{order}', 'hasilLab')->name('hasil-lab');
    });

    Route::controller(RadiologiController::class)->prefix('rad')->name('rad.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{order}', 'show')->name('show');
        Route::get('{order}/eksekusi', 'eksekusiForm')->name('eksekusi.form');
        Route::post('{order}/eksekusi', 'eksekusiStore')->name('eksekusi.store');
        Route::get('{order}/bacaan', 'bacaanForm')->name('bacaan.form');
        Route::post('{order}/bacaan', 'bacaanStore')->name('bacaan.store');
        Route::post('{order}/validate', 'validasi')->name('validate');
        Route::get('image/{image}', 'viewImage')->name('image.view');
        Route::delete('image/{image}', 'deleteImage')->name('image.delete');
    });

    // ICD-10 search (di luar group igd biar bisa dipakai modul lain)
    Route::get('/icd10/search', [\App\Http\Controllers\IgdController::class, 'icd10Search'])->name('icd10.search');

    // Hapus diagnosa
    Route::delete('/diagnosa/{diagnosa}', [\App\Http\Controllers\DiagnosaController::class, 'destroy'])->name('diagnosa.destroy');
});
