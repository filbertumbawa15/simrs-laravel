<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poli', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->string('lokasi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dokter', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique()->comment('Kode internal RS');
            $table->string('sip', 50)->unique()->comment('Surat Izin Praktik');
            $table->string('nik', 16)->nullable();
            $table->string('nama');
            $table->string('gelar_depan', 20)->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->string('spesialisasi');
            $table->string('telp', 20)->nullable();
            $table->string('email')->nullable();
            $table->decimal('jasa_konsul', 12, 2)->default(0);
            $table->string('ihs_id', 50)->nullable()->comment('IHS ID untuk SATUSEHAT');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'spesialisasi']);
        });

        Schema::create('jadwal_dokter', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dokter_id')->constrained('dokter')->restrictOnDelete();
            $table->foreignUuid('poli_id')->constrained('poli')->restrictOnDelete();
            $table->enum('hari', ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedSmallInteger('kuota')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['dokter_id', 'poli_id', 'hari', 'jam_mulai'], 'jadwal_unique');
            $table->index(['hari', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_dokter');
        Schema::dropIfExists('dokter');
        Schema::dropIfExists('poli');
    }
};
