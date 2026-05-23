<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rujukan_keluar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kunjungan_id');
            $table->string('rs_tujuan', 255);
            $table->string('alasan', 50); // enum: FASILITAS_TIDAK_TERSEDIA, dst
            $table->text('catatan');
            $table->string('transportasi', 30)->nullable(); // AMBULANS_RS, AMBULANS_LAIN, KENDARAAN_PRIBADI
            $table->dateTime('tgl_rujuk');
            $table->uuid('dirujuk_oleh');
            $table->text('jawaban_rs_tujuan')->nullable(); // diisi belakangan kalau ada feedback
            $table->dateTime('jawaban_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kunjungan_id')->references('id')->on('kunjungan')->cascadeOnDelete();
            $table->foreign('dirujuk_oleh')->references('id')->on('users');

            $table->index('kunjungan_id');
            $table->index('tgl_rujuk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rujukan_keluar');
    }
};
