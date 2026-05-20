<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_lab', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_order', 30)->unique();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignUuid('dokter_id')->constrained('dokter')->restrictOnDelete();
            $table->dateTime('tgl_order');
            $table->enum('prioritas', ['RUTIN', 'CITO'])->default('RUTIN');
            $table->string('status', 30)->default('DIORDER');
            $table->text('catatan_klinis')->nullable();
            $table->text('diagnosa_kerja')->nullable();
            $table->foreignUuid('sampling_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('sampling_at')->nullable();
            $table->foreignUuid('validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'prioritas', 'tgl_order']);
        });

        Schema::create('order_lab_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('order_lab')->cascadeOnDelete();
            $table->foreignUuid('parameter_id')->constrained('parameter_lab')->restrictOnDelete();
            $table->decimal('tarif', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['order_id', 'parameter_id']);
        });

        Schema::create('hasil_lab', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('order_lab')->cascadeOnDelete();
            $table->foreignUuid('parameter_id')->constrained('parameter_lab')->restrictOnDelete();
            $table->string('hasil');
            $table->decimal('hasil_numerik', 12, 4)->nullable();
            $table->string('satuan', 30)->nullable();
            $table->string('nilai_rujukan')->nullable();
            $table->enum('flag', ['N', 'L', 'H', 'LL', 'HH', 'A'])->default('N');
            $table->text('catatan')->nullable();
            $table->boolean('critical_notified')->default(false)->comment('Untuk LL/HH wajib notifikasi DPJP');
            $table->dateTime('critical_notified_at')->nullable();
            $table->foreignUuid('input_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'parameter_id']);
            $table->index('flag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_lab');
        Schema::dropIfExists('order_lab_detail');
        Schema::dropIfExists('order_lab');
    }
};
