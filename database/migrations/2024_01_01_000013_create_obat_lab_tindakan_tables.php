<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->string('nama_generik')->nullable();
            $table->enum('golongan', ['BEBAS', 'BEBAS_TERBATAS', 'KERAS', 'PSIKOTROPIKA', 'NARKOTIKA']);
            $table->string('bentuk_sediaan', 50)->comment('Tablet, kapsul, sirup, ampul, vial, infus');
            $table->string('satuan', 20);
            $table->string('kekuatan', 50)->nullable()->comment('500mg, 250mg/5ml, dll');
            $table->string('kode_kfa', 50)->nullable()->comment('Kode Kamus Farmasi Alkes untuk SATUSEHAT');
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->boolean('is_fornas')->default(false)->comment('Formularium Nasional');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('golongan');
            $table->fullText(['kode', 'nama', 'nama_generik']);
        });

        Schema::create('stok_obat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('obat_id')->constrained('obat')->restrictOnDelete();
            $table->string('no_batch', 50);
            $table->unsignedInteger('jumlah_masuk');
            $table->unsignedInteger('jumlah_sisa');
            $table->date('tgl_masuk');
            $table->date('exp_date');
            $table->decimal('hpp', 12, 2)->default(0)->comment('Harga Pokok Penjualan per unit');
            $table->string('supplier')->nullable();
            $table->string('no_faktur', 50)->nullable();
            $table->timestamps();

            $table->index(['obat_id', 'exp_date']);
            $table->index('exp_date');
        });

        Schema::create('parameter_lab', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->string('kategori')->comment('Hematologi, Kimia Klinik, Imunoserologi, Urinalisis, Mikrobiologi');
            $table->string('satuan', 30)->nullable();
            $table->string('rujukan_normal')->nullable();
            $table->decimal('nilai_rujukan_min', 12, 4)->nullable();
            $table->decimal('nilai_rujukan_max', 12, 4)->nullable();
            $table->decimal('nilai_kritis_low', 12, 4)->nullable();
            $table->decimal('nilai_kritis_high', 12, 4)->nullable();
            $table->enum('tipe_hasil', ['NUMERIK', 'TEKS', 'KUALITATIF'])->default('NUMERIK');
            $table->string('loinc_code', 20)->nullable()->comment('LOINC untuk SATUSEHAT');
            $table->decimal('tarif', 12, 2)->default(0);
            $table->unsignedSmallInteger('tat_minutes')->default(60)->comment('Turn around time target');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['kategori', 'is_active']);
        });

        Schema::create('paket_lab', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('tarif', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('paket_lab_parameter', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('paket_id')->constrained('paket_lab')->cascadeOnDelete();
            $table->foreignUuid('parameter_id')->constrained('parameter_lab')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['paket_id', 'parameter_id']);
        });

        Schema::create('tindakan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->string('kategori');
            $table->string('icd9_kode', 10)->nullable();
            $table->decimal('tarif_vip', 12, 2)->default(0);
            $table->decimal('tarif_kelas1', 12, 2)->default(0);
            $table->decimal('tarif_kelas2', 12, 2)->default(0);
            $table->decimal('tarif_kelas3', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['kategori', 'is_active']);
            $table->foreign('icd9_kode')->references('kode')->on('icd9cm')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindakan');
        Schema::dropIfExists('paket_lab_parameter');
        Schema::dropIfExists('paket_lab');
        Schema::dropIfExists('parameter_lab');
        Schema::dropIfExists('stok_obat');
        Schema::dropIfExists('obat');
    }
};
