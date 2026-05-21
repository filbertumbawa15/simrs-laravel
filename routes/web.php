<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\RawatJalanController;
use App\Http\Controllers\ResepController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (auth)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
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
});
