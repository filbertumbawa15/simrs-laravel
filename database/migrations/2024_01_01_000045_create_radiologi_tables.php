<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_radiologi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->enum('modalitas', ['XRAY', 'USG', 'CT', 'MRI', 'FLUOROSCOPY', 'MAMMOGRAFI'])->default('XRAY');
            $table->string('region', 50)->nullable()->comment('Thorax, Abdomen, Kepala, Ekstremitas, dll');
            $table->decimal('tarif_vip', 12, 2)->default(0);
            $table->decimal('tarif_kelas1', 12, 2)->default(0);
            $table->decimal('tarif_kelas2', 12, 2)->default(0);
            $table->decimal('tarif_kelas3', 12, 2)->default(0);
            $table->text('template_bacaan')->nullable()->comment('Template default untuk radiolog');
            $table->unsignedSmallInteger('tat_minutes')->default(120)->comment('Target turn-around time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['modalitas', 'is_active']);
        });

        Schema::create('order_radiologi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_order', 30)->unique();
            $table->foreignUuid('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignUuid('dokter_id')->constrained('dokter')->restrictOnDelete()->comment('Dokter perujuk');
            $table->dateTime('tgl_order');
            $table->enum('prioritas', ['RUTIN', 'CITO'])->default('RUTIN');
            $table->string('status', 30)->default('DIORDER');
            $table->text('klinis')->nullable()->comment('Keterangan klinis dari dokter perujuk');
            $table->text('diagnosa_kerja')->nullable();
            $table->boolean('hamil')->default(false)->comment('Wajib cek untuk wanita usia subur');
            $table->boolean('persiapan_puasa')->default(false);

            // Eksekusi (radiografer)
            $table->foreignUuid('radiografer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('eksekusi_at')->nullable();
            $table->text('kondisi_teknis')->nullable()->comment('Posisi pasien, kV, mAs, dst');

            // Validasi (radiolog)
            $table->foreignUuid('radiolog_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('validated_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'prioritas', 'tgl_order']);
        });

        Schema::create('order_radiologi_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('order_radiologi')->cascadeOnDelete();
            $table->foreignUuid('pemeriksaan_id')->constrained('pemeriksaan_radiologi')->restrictOnDelete();
            $table->decimal('tarif', 12, 2)->default(0)->comment('Snapshot saat order');
            $table->timestamps();

            $table->unique(['order_id', 'pemeriksaan_id']);
        });

        Schema::create('hasil_radiologi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('order_radiologi')->cascadeOnDelete();
            $table->foreignUuid('pemeriksaan_id')->constrained('pemeriksaan_radiologi')->restrictOnDelete();
            $table->text('bacaan')->nullable()->comment('Expertise / interpretasi radiolog');
            $table->text('kesan')->nullable()->comment('Kesimpulan radiolog');
            $table->text('saran')->nullable();
            $table->boolean('ada_temuan_kritis')->default(false)->comment('Misal: pneumothorax, perdarahan, fraktur tertentu');
            $table->boolean('critical_notified')->default(false);
            $table->dateTime('critical_notified_at')->nullable();
            $table->foreignUuid('radiolog_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'pemeriksaan_id']);
        });

        Schema::create('hasil_radiologi_image', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('order_radiologi')->cascadeOnDelete();
            $table->foreignUuid('hasil_id')->nullable()->constrained('hasil_radiologi')->nullOnDelete();
            $table->string('disk', 30)->default('rad_images');
            $table->string('path');
            $table->string('mime', 50);
            $table->unsignedInteger('size_bytes');
            $table->string('label')->nullable()->comment('Misal: AP view, Lateral view');
            $table->foreignUuid('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_radiologi_image');
        Schema::dropIfExists('hasil_radiologi');
        Schema::dropIfExists('order_radiologi_detail');
        Schema::dropIfExists('order_radiologi');
        Schema::dropIfExists('pemeriksaan_radiologi');
    }
};
