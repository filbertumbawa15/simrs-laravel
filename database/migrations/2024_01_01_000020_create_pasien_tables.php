<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_rm', 10)->unique()->comment('Nomor Rekam Medis');
            $table->string('nik', 16)->nullable()->index();
            $table->string('nama');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('status_pernikahan', ['BELUM_KAWIN', 'KAWIN', 'CERAI_HIDUP', 'CERAI_MATI'])->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('pendidikan', 30)->nullable();
            $table->string('pekerjaan', 50)->nullable();
            $table->text('alamat');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kelurahan', 50)->nullable();
            $table->string('kecamatan', 50)->nullable();
            $table->string('kabupaten', 50)->nullable();
            $table->string('provinsi', 50)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('telp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('gol_darah', 5)->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('kontak_darurat_nama')->nullable();
            $table->string('kontak_darurat_hubungan', 30)->nullable();
            $table->string('kontak_darurat_telp', 20)->nullable();
            $table->string('ihs_id', 50)->nullable()->comment('IHS ID untuk SATUSEHAT');
            $table->string('foto_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nama', 'tgl_lahir']);
            $table->fullText(['nama', 'no_rm', 'nik']);
        });

        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pasien_id')->unique()->constrained('pasien')->cascadeOnDelete();
            $table->text('riwayat_penyakit')->nullable();
            $table->text('riwayat_keluarga')->nullable();
            $table->text('riwayat_pengobatan')->nullable();
            $table->text('alergi_obat')->nullable();
            $table->text('alergi_makanan')->nullable();
            $table->text('kebiasaan')->nullable()->comment('Merokok, alkohol, dll');
            $table->timestamps();
        });

        Schema::create('asuransi_pasien', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pasien_id')->constrained('pasien')->cascadeOnDelete();
            $table->foreignUuid('asuransi_id')->constrained('asuransi')->restrictOnDelete();
            $table->string('no_polis', 50);
            $table->string('nama_pemegang')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('kelas_hak', 10)->nullable()->comment('Kelas BPJS: 1, 2, 3');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pasien_id', 'is_active']);
            $table->unique(['asuransi_id', 'no_polis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asuransi_pasien');
        Schema::dropIfExists('rekam_medis');
        Schema::dropIfExists('pasien');
    }
};
