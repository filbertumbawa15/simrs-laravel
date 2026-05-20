<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icd10', function (Blueprint $table) {
            $table->string('kode', 10)->primary();
            $table->string('nama');
            $table->string('kategori')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kategori');
            $table->fullText(['kode', 'nama']);
        });

        Schema::create('icd9cm', function (Blueprint $table) {
            $table->string('kode', 10)->primary();
            $table->string('nama');
            $table->string('kategori')->nullable()->comment('Untuk tindakan/prosedur');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('asuransi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->enum('tipe', ['BPJS', 'SWASTA', 'KORPORASI']);
            $table->string('kontak')->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asuransi');
        Schema::dropIfExists('icd9cm');
        Schema::dropIfExists('icd10');
    }
};
