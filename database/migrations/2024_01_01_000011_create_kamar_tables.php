<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_kamar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama')->comment('VIP, I, II, III');
            $table->decimal('tarif_per_hari', 12, 2)->default(0);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('kamar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_kamar', 20)->unique();
            $table->foreignUuid('kelas_id')->constrained('kelas_kamar')->restrictOnDelete();
            $table->enum('status', ['TERSEDIA', 'TERISI', 'RESERVED', 'MAINTENANCE', 'KOTOR'])->default('TERSEDIA');
            $table->string('lokasi')->nullable()->comment('Gedung, lantai');
            $table->unsignedTinyInteger('kapasitas')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_active']);
            $table->index('kelas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
        Schema::dropIfExists('kelas_kamar');
    }
};
