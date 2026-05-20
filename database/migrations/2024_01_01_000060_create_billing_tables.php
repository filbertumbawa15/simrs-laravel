<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_tagihan', 30)->unique();
            $table->foreignUuid('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnDelete();
            $table->dateTime('tgl_tagihan');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('diskon', 14, 2)->default(0);
            $table->decimal('ppn', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('dibayar', 14, 2)->default(0);
            $table->decimal('sisa', 14, 2)->default(0);
            $table->decimal('klaim_penjamin', 14, 2)->default(0)->comment('Bagian yang ditanggung penjamin');
            $table->decimal('iur_pasien', 14, 2)->default(0)->comment('Bagian yang dibayar pasien');
            $table->string('status', 30)->default('DRAFT');
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('finalized_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tgl_tagihan']);
        });

        Schema::create('tagihan_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->string('kategori', 50)->comment('KONSULTASI, TINDAKAN, LAB, RADIOLOGI, FARMASI, KAMAR, VISITE, ADMINISTRASI');
            $table->string('referensi_type')->nullable()->comment('Polymorphic: TindakanKunjungan, OrderLab, Resep, KamarInap');
            $table->uuid('referensi_id')->nullable();
            $table->string('deskripsi');
            $table->unsignedSmallInteger('qty')->default(1);
            $table->decimal('harga', 12, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['tagihan_id', 'kategori']);
            $table->index(['referensi_type', 'referensi_id']);
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_pembayaran', 30)->unique();
            $table->foreignUuid('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->dateTime('tgl_bayar');
            $table->enum('metode', ['TUNAI', 'DEBIT', 'KREDIT', 'TRANSFER', 'QRIS', 'BPJS', 'ASURANSI']);
            $table->decimal('jumlah', 14, 2);
            $table->string('referensi_eksternal', 100)->nullable()->comment('No referensi EDC, transfer, dll');
            $table->foreignUuid('kasir_id')->constrained('users')->restrictOnDelete();
            $table->text('catatan')->nullable();
            $table->boolean('is_void')->default(false);
            $table->foreignUuid('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('void_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['tagihan_id', 'is_void']);
            $table->index('tgl_bayar');
        });

        Schema::create('klaim_bpjs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tagihan_id')->unique()->constrained('tagihan')->cascadeOnDelete();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->string('no_sep', 50);
            $table->string('inacbg_kode', 20)->nullable()->comment('Kode INA-CBGs');
            $table->string('inacbg_deskripsi')->nullable();
            $table->decimal('tarif_inacbg', 14, 2)->default(0);
            $table->decimal('tarif_rs', 14, 2)->default(0);
            $table->decimal('selisih', 14, 2)->default(0);
            $table->enum('status', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK', 'DIBAYAR'])->default('DRAFT');
            $table->dateTime('diajukan_at')->nullable();
            $table->dateTime('dibayar_at')->nullable();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klaim_bpjs');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('tagihan_detail');
        Schema::dropIfExists('tagihan');
    }
};
