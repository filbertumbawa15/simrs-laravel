<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resep', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_resep', 30)->unique();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignUuid('dokter_id')->constrained('dokter')->restrictOnDelete();
            $table->dateTime('tgl_resep');
            $table->string('status', 30)->default('BARU')->comment('BARU, DIVERIFIKASI, DISIAPKAN, DISERAHKAN, BATAL');
            $table->boolean('is_racikan')->default(false);
            $table->text('catatan')->nullable();
            $table->foreignUuid('apoteker_verifikator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->foreignUuid('penyerah_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diserahkan_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'tgl_resep']);
        });

        Schema::create('resep_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resep_id')->constrained('resep')->cascadeOnDelete();
            $table->foreignUuid('obat_id')->constrained('obat')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->string('signa', 50)->comment('3x1, 2x1, k/p, dll');
            $table->string('aturan_pakai', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('harga_satuan', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->boolean('is_diserahkan')->default(false);
            $table->json('batch_used')->nullable()->comment('Tracking batch yang dipakai untuk FEFO');
            $table->timestamps();
        });

        Schema::create('mutasi_stok_obat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stok_id')->constrained('stok_obat')->restrictOnDelete();
            $table->foreignUuid('obat_id')->constrained('obat')->restrictOnDelete();
            $table->enum('jenis', ['MASUK', 'KELUAR', 'PENYESUAIAN', 'RUSAK', 'EXPIRED']);
            $table->foreignUuid('resep_detail_id')->nullable()->constrained('resep_detail')->nullOnDelete();
            $table->integer('jumlah')->comment('+ masuk, - keluar');
            $table->integer('saldo_sebelum');
            $table->integer('saldo_sesudah');
            $table->string('referensi')->nullable();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['obat_id', 'created_at']);
        });

        Schema::create('tindakan_kunjungan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignUuid('tindakan_id')->constrained('tindakan')->restrictOnDelete();
            $table->foreignUuid('petugas_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('dokter_id')->nullable()->constrained('dokter')->nullOnDelete();
            $table->dateTime('waktu_tindakan');
            $table->unsignedSmallInteger('qty')->default(1);
            $table->decimal('tarif', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['kunjungan_id', 'waktu_tindakan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindakan_kunjungan');
        Schema::dropIfExists('mutasi_stok_obat');
        Schema::dropIfExists('resep_detail');
        Schema::dropIfExists('resep');
    }
};
