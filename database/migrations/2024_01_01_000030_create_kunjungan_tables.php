<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_kunjungan', 30)->unique();
            $table->foreignUuid('pasien_id')->constrained('pasien')->restrictOnDelete();
            $table->enum('tipe', ['RJ', 'RI', 'IGD']);
            $table->dateTime('tgl_masuk');
            $table->dateTime('tgl_keluar')->nullable();
            $table->string('status', 30)->default('TERDAFTAR');
            $table->enum('penjamin', ['UMUM', 'BPJS', 'ASURANSI'])->default('UMUM');
            $table->foreignUuid('asuransi_pasien_id')->nullable()->constrained('asuransi_pasien')->nullOnDelete();
            $table->string('no_rujukan', 50)->nullable();
            $table->string('no_sep', 50)->nullable()->comment('Surat Eligibilitas Peserta BPJS');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipe', 'status']);
            $table->index(['pasien_id', 'tgl_masuk']);
            $table->index('tgl_masuk');
        });

        Schema::create('triase_igd', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnDelete();
            $table->enum('kategori', ['MERAH', 'KUNING', 'HIJAU', 'HITAM'])->comment('Resusitasi/Emergent/Urgent/DOA');
            $table->dateTime('waktu_triase');
            $table->foreignUuid('triase_oleh')->constrained('users')->restrictOnDelete();
            $table->text('keluhan_utama');
            $table->json('tanda_vital')->nullable()->comment('TD, N, R, S, SpO2');
            $table->timestamps();

            $table->index('kategori');
        });

        Schema::create('rawat_jalan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignUuid('poli_id')->constrained('poli')->restrictOnDelete();
            $table->foreignUuid('dokter_id')->constrained('dokter')->restrictOnDelete();
            $table->unsignedSmallInteger('no_antrian');
            $table->dateTime('waktu_panggilan')->nullable();
            $table->dateTime('waktu_mulai_periksa')->nullable();
            $table->dateTime('waktu_selesai_periksa')->nullable();

            // SOAP
            $table->text('subjective')->nullable()->comment('S: Keluhan, anamnesa');
            $table->text('objective')->nullable()->comment('O: Pemeriksaan fisik');
            $table->json('tanda_vital')->nullable();
            $table->text('assessment')->nullable()->comment('A: Penilaian (selain dx)');
            $table->text('plan')->nullable()->comment('P: Rencana terapi');
            $table->text('edukasi')->nullable();
            $table->boolean('rujuk_internal')->default(false);
            $table->boolean('rujuk_eksternal')->default(false);
            $table->text('catatan_rujukan')->nullable();

            $table->timestamps();

            $table->index(['poli_id', 'no_antrian']);
        });

        Schema::create('rawat_inap', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignUuid('dpjp_id')->constrained('dokter')->restrictOnDelete()->comment('Dokter Penanggung Jawab Pelayanan');
            $table->dateTime('tgl_masuk_ri');
            $table->dateTime('tgl_pulang')->nullable();
            $table->enum('cara_pulang', ['SEMBUH', 'MEMBAIK', 'BELUM_SEMBUH', 'APS', 'RUJUK', 'MENINGGAL'])->nullable()
                ->comment('APS = Atas Permintaan Sendiri');
            $table->text('alasan_masuk');
            $table->text('resume_medis')->nullable();
            $table->text('instruksi_pulang')->nullable();
            $table->boolean('resume_finalized')->default(false);
            $table->dateTime('resume_finalized_at')->nullable();
            $table->timestamps();

            $table->index('dpjp_id');
        });

        Schema::create('kamar_inap', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rawat_inap_id')->constrained('rawat_inap')->cascadeOnDelete();
            $table->foreignUuid('kamar_id')->constrained('kamar')->restrictOnDelete();
            $table->dateTime('masuk');
            $table->dateTime('keluar')->nullable();
            $table->text('alasan_pindah')->nullable();
            $table->timestamps();

            $table->index(['rawat_inap_id', 'masuk']);
        });

        Schema::create('cppt', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete()
                ->comment('Catatan Perkembangan Pasien Terintegrasi');
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('profesi', ['DOKTER', 'PERAWAT', 'APOTEKER', 'GIZI', 'FISIOTERAPI']);
            $table->dateTime('waktu_catatan');
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->text('instruksi')->nullable();
            $table->json('verified_by')->nullable()->comment('Verifikasi DPJP');
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['kunjungan_id', 'waktu_catatan']);
        });

        Schema::create('diagnosa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->string('icd10_kode', 10);
            $table->enum('tipe', ['PRIMER', 'SEKUNDER', 'KOMPLIKASI']);
            $table->text('catatan')->nullable();
            $table->foreignUuid('dokter_id')->constrained('dokter')->restrictOnDelete();
            $table->timestamps();

            $table->index(['kunjungan_id', 'tipe']);
            $table->foreign('icd10_kode')->references('kode')->on('icd10')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosa');
        Schema::dropIfExists('cppt');
        Schema::dropIfExists('kamar_inap');
        Schema::dropIfExists('rawat_inap');
        Schema::dropIfExists('rawat_jalan');
        Schema::dropIfExists('triase_igd');
        Schema::dropIfExists('kunjungan');
    }
};
