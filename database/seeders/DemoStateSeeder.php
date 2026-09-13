<?php

namespace Database\Seeders;

use App\Enums\Penjamin;
use App\Enums\PrioritasOrder;
use App\Enums\StatusKunjungan;
use App\Enums\StatusOrderLab;
use App\Enums\StatusOrderRadiologi;
use App\Enums\StatusTagihan;
use App\Enums\TipeKunjungan;
use App\Models\Diagnosa;
use App\Models\Dokter;
use App\Models\HasilLab;
use App\Models\Kunjungan;
use App\Models\OrderLab;
use App\Models\OrderLabDetail;
use App\Models\OrderRadiologi;
use App\Models\OrderRadiologiDetail;
use App\Models\ParameterLab;
use App\Models\Pasien;
use App\Models\PemeriksaanRadiologi;
use App\Models\Poli;
use App\Models\RawatJalan;
use App\Models\Resep;
use App\Models\ResepDetail;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\TriaseIgd;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed data "in-progress" untuk capture screenshot user guide.
 * Semua kunjungan/order/resep dibuat di berbagai status untuk demo tiap role.
 */
class DemoStateSeeder extends Seeder
{
    public function run(): void
    {
        activity()->disableLogging();

        try {
            // Pakai pasien existing (Reza Akbar Manurung, mahasiswa, sehat)
            $pasien = Pasien::orderBy('no_rm', 'desc')->first();
            $poli = Poli::where('kode', 'like', 'PU%')->first() ?? Poli::first();
            $dokter = Dokter::where('email', 'like', 'iqbal%')->first() ?? Dokter::first();
            $petugas = User::where('username', 'reg.mira')->first();

            // ============================================================
            // 1. Kunjungan RJ DALAM_PEMERIKSAAN — untuk dokter demo
            // ============================================================
            $kunjunganRj = Kunjungan::create([
                'no_kunjungan' => 'RJ/' . now()->format('Y/m') . '/DEMO1',
                'pasien_id' => $pasien->id,
                'tipe' => TipeKunjungan::RawatJalan,
                'tgl_masuk' => now(),
                'status' => StatusKunjungan::DalamPemeriksaan,
                'penjamin' => Penjamin::Umum,
                'created_by' => $petugas?->id,
            ]);

            RawatJalan::create([
                'kunjungan_id' => $kunjunganRj->id,
                'poli_id' => $poli->id,
                'dokter_id' => $dokter->id,
                'no_antrian' => 1,
                'waktu_panggilan' => now()->subMinutes(5),
                'waktu_mulai_periksa' => now()->subMinutes(3),
            ]);

            // Kunjungan RJ TERDAFTAR (menunggu, untuk demo panggil)
            $pasien2 = Pasien::skip(1)->first();
            $kunjunganMenunggu = Kunjungan::create([
                'no_kunjungan' => 'RJ/' . now()->format('Y/m') . '/DEMO2',
                'pasien_id' => $pasien2->id,
                'tipe' => TipeKunjungan::RawatJalan,
                'tgl_masuk' => now(),
                'status' => StatusKunjungan::Terdaftar,
                'penjamin' => Penjamin::BPJS,
                'created_by' => $petugas?->id,
            ]);
            RawatJalan::create([
                'kunjungan_id' => $kunjunganMenunggu->id,
                'poli_id' => $poli->id,
                'dokter_id' => $dokter->id,
                'no_antrian' => 2,
            ]);

            $pasien3 = Pasien::skip(2)->first();
            $kunjunganMenunggu2 = Kunjungan::create([
                'no_kunjungan' => 'RJ/' . now()->format('Y/m') . '/DEMO3',
                'pasien_id' => $pasien3->id,
                'tipe' => TipeKunjungan::RawatJalan,
                'tgl_masuk' => now(),
                'status' => StatusKunjungan::Terdaftar,
                'penjamin' => Penjamin::Umum,
                'created_by' => $petugas?->id,
            ]);
            RawatJalan::create([
                'kunjungan_id' => $kunjunganMenunggu2->id,
                'poli_id' => $poli->id,
                'dokter_id' => $dokter->id,
                'no_antrian' => 3,
            ]);

            // ============================================================
            // 2. Order lab di berbagai status
            // ============================================================
            $params = ParameterLab::limit(3)->get();

            // DIORDER (untuk analis sampling)
            $orderLabBaru = OrderLab::create([
                'no_order' => 'LAB/' . now()->format('Y/m') . '/DEMO1',
                'kunjungan_id' => $kunjunganRj->id,
                'dokter_id' => $dokter->id,
                'tgl_order' => now(),
                'prioritas' => PrioritasOrder::Cito,
                'status' => StatusOrderLab::Diorder,
                'catatan_klinis' => 'Suspek DHF hari 3 demam. Cek trombosit segera.',
                'diagnosa_kerja' => 'DHF suspect',
            ]);
            foreach ($params as $p) {
                OrderLabDetail::create([
                    'order_id' => $orderLabBaru->id,
                    'parameter_id' => $p->id,
                    'tarif' => $p->tarif,
                ]);
            }

            // VALIDASI (untuk dokter PK — sudah ada hasil, tinggal validate)
            $orderLabValidasi = OrderLab::create([
                'no_order' => 'LAB/' . now()->format('Y/m') . '/DEMO2',
                'kunjungan_id' => $kunjunganMenunggu->id,
                'dokter_id' => $dokter->id,
                'tgl_order' => now()->subHours(1),
                'prioritas' => PrioritasOrder::Rutin,
                'status' => StatusOrderLab::Validasi,
                'catatan_klinis' => 'GDS + Kolesterol kontrol rutin DM tipe 2.',
            ]);
            $analis = User::where('username', 'lab.budi')->first();
            foreach ($params as $i => $p) {
                OrderLabDetail::create([
                    'order_id' => $orderLabValidasi->id,
                    'parameter_id' => $p->id,
                    'tarif' => $p->tarif,
                ]);
                // Simulasi hasil — 1 abnormal untuk demo flag
                $hasil = $i === 0 ? '450' : '110'; // GDS 450 = kritis high
                HasilLab::create([
                    'order_id' => $orderLabValidasi->id,
                    'parameter_id' => $p->id,
                    'hasil' => $hasil,
                    'hasil_numerik' => (float) $hasil,
                    'satuan' => $p->satuan,
                    'nilai_rujukan' => $p->rujukan_normal,
                    'flag' => $p->evaluateFlag((float) $hasil),
                    'input_oleh' => $analis?->id,
                ]);
            }

            // ============================================================
            // 3. Order radiologi DIORDER (untuk radiografer eksekusi)
            // ============================================================
            $pemeriksaanRad = PemeriksaanRadiologi::first();
            if ($pemeriksaanRad) {
                $orderRad = OrderRadiologi::create([
                    'no_order' => 'RAD/' . now()->format('Y/m') . '/DEMO1',
                    'kunjungan_id' => $kunjunganRj->id,
                    'dokter_id' => $dokter->id,
                    'tgl_order' => now(),
                    'prioritas' => PrioritasOrder::Rutin,
                    'status' => StatusOrderRadiologi::Diorder,
                    'klinis' => 'Batuk 2 minggu tidak sembuh dengan antibiotik lini pertama. Cek infiltrat.',
                    'diagnosa_kerja' => 'Suspek pneumonia',
                    'hamil' => false,
                    'persiapan_puasa' => false,
                ]);
                OrderRadiologiDetail::create([
                    'order_id' => $orderRad->id,
                    'pemeriksaan_id' => $pemeriksaanRad->id,
                    'tarif' => $pemeriksaanRad->tarif_kelas3,
                ]);
            }

            // ============================================================
            // 4. Resep di berbagai status
            // ============================================================
            $obat = \App\Models\Obat::where('nama', 'like', '%Paracetamol%')->first() ?? \App\Models\Obat::first();

            // BARU (untuk apoteker verifikasi)
            $resepBaru = Resep::create([
                'no_resep' => 'RX/' . now()->format('Y/m') . '/DEMO1',
                'kunjungan_id' => $kunjunganRj->id,
                'dokter_id' => $dokter->id,
                'tgl_resep' => now(),
                'status' => 'BARU',
                'catatan' => 'Terapi simptomatik. Kontrol jika demam >3 hari.',
            ]);
            ResepDetail::create([
                'resep_id' => $resepBaru->id,
                'obat_id' => $obat->id,
                'jumlah' => 10,
                'signa' => '3x1',
                'aturan_pakai' => 'sesudah makan',
                'harga_satuan' => $obat->harga_jual,
                'subtotal' => $obat->harga_jual * 10,
            ]);

            // DIVERIFIKASI (untuk apoteker/TTK dispense)
            $apoteker = User::where('username', 'apt.fitri')->first();
            $resepVerif = Resep::create([
                'no_resep' => 'RX/' . now()->format('Y/m') . '/DEMO2',
                'kunjungan_id' => $kunjunganMenunggu->id,
                'dokter_id' => $dokter->id,
                'tgl_resep' => now()->subMinutes(15),
                'status' => 'DIVERIFIKASI',
                'apoteker_verifikator_id' => $apoteker?->id,
                'verified_at' => now()->subMinutes(5),
                'catatan' => 'Sudah dicek — tidak ada interaksi.',
            ]);
            ResepDetail::create([
                'resep_id' => $resepVerif->id,
                'obat_id' => $obat->id,
                'jumlah' => 15,
                'signa' => '3x1',
                'aturan_pakai' => 'sebelum makan',
                'harga_satuan' => $obat->harga_jual,
                'subtotal' => $obat->harga_jual * 15,
            ]);

            // ============================================================
            // 5. Tagihan BELUM_LUNAS (untuk kasir demo)
            // ============================================================
            $tagihanBelum = Tagihan::create([
                'no_tagihan' => 'INV/' . now()->format('Y/m') . '/DEMO1',
                'kunjungan_id' => $kunjunganMenunggu2->id,
                'tgl_tagihan' => now(),
                'status' => StatusTagihan::BelumLunas,
                'subtotal' => 175000,
                'total' => 175000,
                'sisa' => 175000,
            ]);
            TagihanDetail::create([
                'tagihan_id' => $tagihanBelum->id,
                'kategori' => 'KONSULTASI',
                'deskripsi' => 'Konsultasi ' . $dokter->nama_lengkap,
                'qty' => 1,
                'harga' => 100000,
                'subtotal' => 100000,
            ]);
            TagihanDetail::create([
                'tagihan_id' => $tagihanBelum->id,
                'kategori' => 'FARMASI',
                'deskripsi' => 'Paracetamol 500mg (3x1)',
                'qty' => 15,
                'harga' => 5000,
                'subtotal' => 75000,
            ]);

            // ============================================================
            // 6. Kunjungan IGD baru untuk demo triase
            // ============================================================
            $pasienIgd = Pasien::skip(3)->first();
            $kunjunganIgd = Kunjungan::create([
                'no_kunjungan' => 'IGD/' . now()->format('Y/m') . '/DEMO1',
                'pasien_id' => $pasienIgd->id,
                'tipe' => TipeKunjungan::IGD,
                'tgl_masuk' => now()->subMinutes(2),
                'status' => StatusKunjungan::Terdaftar,
                'penjamin' => Penjamin::BPJS,
                'created_by' => $petugas?->id,
            ]);

            // IGD yang sudah ditriase (KUNING)
            $pasienIgd2 = Pasien::skip(4)->first();
            $kunjunganIgd2 = Kunjungan::create([
                'no_kunjungan' => 'IGD/' . now()->format('Y/m') . '/DEMO2',
                'pasien_id' => $pasienIgd2->id,
                'tipe' => TipeKunjungan::IGD,
                'tgl_masuk' => now()->subMinutes(15),
                'status' => StatusKunjungan::DalamPemeriksaan,
                'penjamin' => Penjamin::Umum,
                'created_by' => $petugas?->id,
            ]);
            $perawatIgd = User::where('username', 'pwt.rina')->first();
            TriaseIgd::create([
                'kunjungan_id' => $kunjunganIgd2->id,
                'kategori' => 'KUNING',
                'waktu_triase' => now()->subMinutes(13),
                'triase_oleh' => $perawatIgd?->id,
                'keluhan_utama' => 'Nyeri dada kiri sejak 30 menit lalu, keringat dingin.',
                'tanda_vital' => [
                    'td_sistol' => 145, 'td_diastol' => 95,
                    'nadi' => 105, 'respirasi' => 22,
                    'suhu' => 36.8, 'spo2' => 96, 'gcs' => 15,
                ],
            ]);

            $this->command->newLine();
            $this->command->info('  [OK] Demo state seeded untuk capture screenshot user guide.');
        } finally {
            activity()->enableLogging();
        }
    }
}
