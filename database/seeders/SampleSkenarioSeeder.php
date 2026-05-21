<?php

namespace Database\Seeders;

use App\Enums\FlagHasilLab;
use App\Enums\MetodePembayaran;
use App\Enums\Penjamin;
use App\Enums\PrioritasOrder;
use App\Enums\StatusKamar;
use App\Enums\StatusKunjungan;
use App\Enums\StatusOrderLab;
use App\Enums\StatusOrderRadiologi;
use App\Enums\StatusTagihan;
use App\Enums\TipeKunjungan;
use App\Models\Cppt;
use App\Models\Diagnosa;
use App\Models\Dokter;
use App\Models\HasilLab;
use App\Models\HasilRadiologi;
use App\Models\Icd10;
use App\Models\Kamar;
use App\Models\KamarInap;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\OrderLab;
use App\Models\OrderLabDetail;
use App\Models\OrderRadiologi;
use App\Models\OrderRadiologiDetail;
use App\Models\ParameterLab;
use App\Models\Pasien;
use App\Models\PemeriksaanRadiologi;
use App\Models\Pembayaran;
use App\Models\Poli;
use App\Models\RawatInap;
use App\Models\RawatJalan;
use App\Models\Resep;
use App\Models\ResepDetail;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\TindakanKunjungan;
use App\Models\Tindakan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeder skenario lengkap end-to-end.
 * 5 skenario yang cover semua modul utama.
 *
 * Jalankan: php artisan db:seed --class=SampleSkenarioSeeder
 *
 * PRASYARAT (sudah ada dari seeder sebelumnya):
 * - Users (dr.andika, dr.sari, dr.iqbal, dr.doni, dr.dewi, apt.fitri, lab.budi, rad.tono, kasir.lina, reg.mira)
 * - Pasien (10 showcase fixed)
 * - Master: poli, dokter, kamar, obat, ICD10, parameter lab, pemeriksaan radiologi
 */
class SampleSkenarioSeeder extends Seeder
{
    public function run(): void
    {
        // Disable activity log selama seeding
        activity()->disableLogging();

        try {
            $this->command->info('🏥 Membangun skenario sample data...');
            $this->command->newLine();

            // Pre-fetch master data yang dipakai berulang
            $this->preload();

            DB::transaction(function () {
                $this->skenario1_RjUmumSembuh();
                $this->skenario2_RjBpjsLab();
                $this->skenario3_RjRadiologi();
                $this->skenario4_IgdRanap();
                $this->skenario5_RjCicilan();
            });

            $this->command->newLine();
            $this->command->info('✅ 5 skenario berhasil di-seed.');
            $this->printSummary();
        } finally {
            activity()->enableLogging();
        }
    }

    // ==========================================================
    // PRE-LOAD MASTER DATA
    // ==========================================================
    protected array $cache = [];

    protected function preload(): void
    {
        $this->cache['users'] = User::all()->keyBy('username');
        $this->cache['dokter'] = Dokter::all();
        $this->cache['poli'] = Poli::all()->keyBy('kode');
        $this->cache['pasien'] = Pasien::orderBy('no_rm')->take(10)->get();
        $this->cache['icd10'] = Icd10::all()->keyBy('kode');
        $this->cache['obat'] = Obat::all()->keyBy('kode');
        $this->cache['paramlab'] = ParameterLab::all()->keyBy('kode');
        $this->cache['pemrad'] = PemeriksaanRadiologi::all()->keyBy('kode');
        $this->cache['tindakan'] = Tindakan::all()->keyBy('kode');
    }

    protected function pasien(int $i): Pasien
    {
        return $this->cache['pasien'][$i];
    }

    protected function user(string $username): User
    {
        if (! isset($this->cache['users'][$username])) {
            throw new \RuntimeException("User '{$username}' tidak ditemukan. Run UserSeeder dulu.");
        }
        return $this->cache['users'][$username];
    }

    protected function dokterByUsername(string $username): Dokter
    {
        // Hubungkan user → dokter by name (simplistic). Adjust jika punya mapping lain.
        $user = $this->user($username);
        $dokter = $this->cache['dokter']->firstWhere('nama_lengkap', $user->name)
            ?? $this->cache['dokter']->first();
        return $dokter;
    }

    // ==========================================================
    // SKENARIO 1: RJ UMUM SEMBUH (paling simple)
    // Bp. Budi Sitorus, 45 thn, Poli Umum, Common Cold, bayar tunai lunas
    // ==========================================================
    protected function skenario1_RjUmumSembuh(): void
    {
        $pasien = $this->pasien(0);
        $dokter = $this->dokterByUsername('dr.iqbal');
        $poli = $this->cache['poli']['UMUM'] ?? $this->cache['poli']->first();

        // 1. Kunjungan
        $kunjungan = Kunjungan::create([
            'no_kunjungan' => $this->genNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tipe' => TipeKunjungan::RawatJalan,
            'penjamin' => Penjamin::Umum,
            'tgl_masuk' => now()->subDays(3)->setTime(8, 15),
            'tgl_keluar' => now()->subDays(3)->setTime(10, 30),
            'status' => StatusKunjungan::Selesai,
            'created_by' => $this->user('reg.mira')->id,
        ]);

        // 2. RJ + SOAP
        $rj = RawatJalan::create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => $poli->id,
            'dokter_id' => $dokter->id,
            'no_antrian' => 1,
            'waktu_panggilan' => $kunjungan->tgl_masuk->copy()->addMinutes(15),
            'waktu_mulai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(20),
            'waktu_selesai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(35),
            'tanda_vital' => [
                'td_sistol' => 120,
                'td_diastol' => 80,
                'nadi' => 78,
                'respirasi' => 18,
                'suhu' => 37.2,
                'spo2' => 98,
                'bb' => 70,
                'tb' => 168,
            ],
            'subjective' => "Pilek 3 hari, bersin-bersin, hidung tersumbat. Demam ringan kadang. Nafsu makan baik.",
            'objective' => "KU baik, kompos mentis. THT: mukosa hidung edema, sekret serous. Faring tidak hiperemis. Paru vesikuler, tidak ada ronkhi/wheezing.",
            'assessment' => "Common cold (rhinitis akut viral)",
            'plan' => "Simptomatik: parasetamol prn, dekongestan. Istirahat cukup, banyak minum air.",
            'edukasi' => "Cuci tangan rutin. Hindari kontak dekat dengan keluarga. Kontrol jika demam tinggi > 38.5 atau sesak.",
        ]);

        // 3. Diagnosa
        $this->addDiagnosa($kunjungan, 'J00', $dokter, 'PRIMER');

        // 4. Resep (parasetamol + pseudoephedrine)
        $resep = $this->buatResep($kunjungan, $dokter, [
            ['kode' => 'OBT001', 'jumlah' => 10, 'signa' => '3x1', 'aturan' => 'Sesudah makan'],
            ['kode' => 'OBT002', 'jumlah' => 10, 'signa' => '3x1', 'aturan' => 'Sesudah makan'],
        ], status: 'DISERAHKAN');

        // 5. Tagihan & pembayaran
        $tagihan = $this->buatTagihan($kunjungan, [
            ['kategori' => 'JASA_DOKTER', 'desc' => 'Konsultasi Dokter Umum', 'qty' => 1, 'harga' => 75000],
            ['kategori' => 'ADMINISTRASI', 'desc' => 'Biaya Pendaftaran', 'qty' => 1, 'harga' => 15000],
            ['kategori' => 'OBAT', 'desc' => 'Resep ' . $resep->no_resep, 'qty' => 1, 'harga' => (float) $resep->total],
        ], finalized: true);

        $this->buatPembayaran($tagihan, MetodePembayaran::Tunai, (float) $tagihan->total);

        $this->command->line('  ✓ Skenario 1 — RJ Umum: ' . $pasien->nama . ' (' . $kunjungan->no_kunjungan . ')');
    }

    // ==========================================================
    // SKENARIO 2: RJ BPJS dengan LAB (CBC abnormal)
    // Ibu Maria Sinaga, 52 thn, Poli Penyakit Dalam, DM tipe 2, lab GDP+HbA1c+kolesterol
    // ==========================================================
    protected function skenario2_RjBpjsLab(): void
    {
        $pasien = $this->pasien(1);
        $dokter = $this->dokterByUsername('dr.andika'); // Sp.PD
        $poli = $this->cache['poli']['INTERNA'] ?? $this->cache['poli']->skip(1)->first() ?? $this->cache['poli']->first();

        $kunjungan = Kunjungan::create([
            'no_kunjungan' => $this->genNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tipe' => TipeKunjungan::RawatJalan,
            'penjamin' => Penjamin::BPJS,
            'no_sep' => '1234R001' . now()->format('ymd') . 'V001',
            'no_rujukan' => '0001R001' . now()->format('ymd') . '001',
            'tgl_masuk' => now()->subDays(2)->setTime(9, 0),
            'tgl_keluar' => now()->subDays(2)->setTime(12, 0),
            'status' => StatusKunjungan::Selesai,
            'created_by' => $this->user('reg.mira')->id,
        ]);

        $rj = RawatJalan::create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => $poli->id,
            'dokter_id' => $dokter->id,
            'no_antrian' => 5,
            'waktu_mulai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(30),
            'waktu_selesai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(50),
            'tanda_vital' => [
                'td_sistol' => 145,
                'td_diastol' => 90,
                'nadi' => 82,
                'respirasi' => 18,
                'suhu' => 36.6,
                'spo2' => 97,
                'bb' => 72,
                'tb' => 158,
            ],
            'subjective' => "Kontrol DM rutin. Sering haus dan BAK terutama malam. Lemas. Riwayat DM 5 tahun, minum metformin tidak teratur.",
            'objective' => "KU sedang. Akral hangat, CRT < 2 detik. Tidak ada tanda dehidrasi berat. Abdomen supel, BU normal.",
            'assessment' => "DM tipe 2 tidak terkontrol + hipertensi grade I",
            'plan' => "Order lab: GDP, HbA1c, kolesterol total, kreatinin. Optimalisasi terapi DM dan HT.",
            'edukasi' => "Edukasi diet DM, kepatuhan obat, monitoring gula darah mandiri 2x/minggu.",
        ]);

        $this->addDiagnosa($kunjungan, 'E11', $dokter, 'PRIMER');
        $this->addDiagnosa($kunjungan, 'I10', $dokter, 'SEKUNDER');

        // Order LAB dengan hasil abnormal
        $orderLab = $this->buatOrderLabLengkap($kunjungan, $dokter, [
            'GDP' => ['hasil' => '210', 'flag' => 'H'],
            'HBA1C' => ['hasil' => '9.2', 'flag' => 'H'],
            'KOLESTEROL' => ['hasil' => '245', 'flag' => 'H'],
            'KREATININ' => ['hasil' => '1.1', 'flag' => 'N'],
        ], catatan: 'Pasien DM kontrol rutin, kontrol terapi');

        // Resep DM + HT
        $resep = $this->buatResep($kunjungan, $dokter, [
            ['kode' => 'OBT003', 'jumlah' => 30, 'signa' => '2x1', 'aturan' => 'Sesudah makan'],
            ['kode' => 'OBT004', 'jumlah' => 30, 'signa' => '1x1', 'aturan' => 'Pagi hari'],
        ], status: 'DISERAHKAN');

        // Tagihan BPJS (semua dijamin, total 0 ke pasien)
        $tagihan = $this->buatTagihan($kunjungan, [
            ['kategori' => 'JASA_DOKTER', 'desc' => 'Konsultasi Sp.PD', 'qty' => 1, 'harga' => 150000],
            ['kategori' => 'LABORATORIUM', 'desc' => 'Paket Lab DM Lengkap', 'qty' => 1, 'harga' => 285000],
            ['kategori' => 'OBAT', 'desc' => 'Resep ' . $resep->no_resep, 'qty' => 1, 'harga' => (float) $resep->total],
        ], finalized: true, status: StatusTagihan::Klaim);

        $this->command->line('  ✓ Skenario 2 — RJ BPJS+Lab: ' . $pasien->nama . ' (HbA1c ' . '9.2% kritis)');
    }

    // ==========================================================
    // SKENARIO 3: RJ dengan RADIOLOGI (temuan kritis pneumonia)
    // Bp. Heri Manurung, 60 thn, Poli Paru, batuk + demam, Thorax PA → pneumonia
    // ==========================================================
    protected function skenario3_RjRadiologi(): void
    {
        $pasien = $this->pasien(2);
        $dokter = $this->dokterByUsername('dr.andika');
        $poli = $this->cache['poli']['INTERNA'] ?? $this->cache['poli']->first();

        $kunjungan = Kunjungan::create([
            'no_kunjungan' => $this->genNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tipe' => TipeKunjungan::RawatJalan,
            'penjamin' => Penjamin::Umum,
            'tgl_masuk' => now()->subDays(1)->setTime(10, 0),
            'tgl_keluar' => now()->subDays(1)->setTime(13, 30),
            'status' => StatusKunjungan::Selesai,
            'created_by' => $this->user('reg.mira')->id,
        ]);

        $rj = RawatJalan::create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => $poli->id,
            'dokter_id' => $dokter->id,
            'no_antrian' => 8,
            'waktu_mulai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(30),
            'waktu_selesai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(50),
            'tanda_vital' => [
                'td_sistol' => 130,
                'td_diastol' => 85,
                'nadi' => 96,
                'respirasi' => 22,
                'suhu' => 38.5,
                'spo2' => 94,
                'bb' => 65,
                'tb' => 165,
            ],
            'subjective' => "Batuk berdahak 1 minggu, dahak kuning kental. Demam naik turun, sesak ringan saat aktivitas. Riwayat merokok 30 tahun.",
            'objective' => "KU sedang. RR 22 takipnea ringan. Auskultasi paru: ronkhi basah kasar lapangan paru kanan bawah. Perkusi redup paru kanan bawah.",
            'assessment' => "Suspek pneumonia komunitas (CAP) - perlu konfirmasi radiologis",
            'plan' => "Foto Thorax PA cito. Antibiotik empiris levofloxacin. Mukolitik. Antipiretik.",
        ]);

        $this->addDiagnosa($kunjungan, 'J18', $dokter, 'PRIMER'); // Pneumonia unspecified

        // Order Radiologi
        $orderRad = $this->buatOrderRadiologiLengkap(
            $kunjungan,
            $dokter,
            ['XR001'], // Thorax PA
            prioritas: PrioritasOrder::Cito,
            klinis: 'Batuk berdahak 1 minggu, demam, ronkhi kanan bawah. R/o pneumonia.',
            bacaan: [
                'XR001' => [
                    'bacaan' => "Cor: ukuran dan bentuk dalam batas normal, CTR < 50%.\nPulmo: Tampak infiltrat di lobus inferior paru kanan dengan air bronchogram (+).\nTidak tampak efusi pleura.\nDiafragma dan sinus costofrenicus kanan tertutup infiltrat, kiri tajam.\nTulang-tulang costae intact.",
                    'kesan' => 'Gambaran konsolidasi lobus inferior paru kanan - mendukung pneumonia',
                    'saran' => 'Korelasi klinis dan laboratorium. Foto kontrol setelah terapi adekuat.',
                    'kritis' => true,
                ],
            ],
        );

        // Resep antibiotik
        $resep = $this->buatResep($kunjungan, $dokter, [
            ['kode' => 'OBT005', 'jumlah' => 7, 'signa' => '1x1', 'aturan' => 'Sesudah makan'],
            ['kode' => 'OBT001', 'jumlah' => 15, 'signa' => '3x1', 'aturan' => 'Prn demam'],
            ['kode' => 'OBT006', 'jumlah' => 15, 'signa' => '3x1', 'aturan' => 'Sesudah makan'],
        ], status: 'DISERAHKAN');

        $tagihan = $this->buatTagihan($kunjungan, [
            ['kategori' => 'JASA_DOKTER', 'desc' => 'Konsultasi Sp.PD', 'qty' => 1, 'harga' => 150000],
            ['kategori' => 'RADIOLOGI', 'desc' => 'Thorax PA (CITO)', 'qty' => 1, 'harga' => 95000],
            ['kategori' => 'OBAT', 'desc' => 'Resep ' . $resep->no_resep, 'qty' => 1, 'harga' => (float) $resep->total],
            ['kategori' => 'ADMINISTRASI', 'desc' => 'Biaya Pendaftaran', 'qty' => 1, 'harga' => 15000],
        ], finalized: true);

        $this->buatPembayaran($tagihan, MetodePembayaran::QRIS, (float) $tagihan->total);

        $this->command->line('  ✓ Skenario 3 — RJ + Rontgen: ' . $pasien->nama . ' (pneumonia, temuan kritis)');
    }

    // ==========================================================
    // SKENARIO 4: IGD → RANAP (paling kompleks)
    // Ibu Lisa Tobing, 38 thn, IGD: nyeri perut hebat → admisi RI Bedah → CPPT → pulang
    // ==========================================================
    protected function skenario4_IgdRanap(): void
    {
        $pasien = $this->pasien(3);
        $dokterIgd = $this->dokterByUsername('dr.iqbal');
        $dpjp = $this->dokterByUsername('dr.andika');

        // IGD Kunjungan
        $kunjunganIgd = Kunjungan::create([
            'no_kunjungan' => $this->genNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tipe' => TipeKunjungan::IGD,
            'penjamin' => Penjamin::BPJS,
            'no_sep' => '1234R001' . now()->format('ymd') . 'I001',
            'tgl_masuk' => now()->subDays(5)->setTime(22, 30),
            'status' => StatusKunjungan::DalamPemeriksaan,
            'created_by' => $this->user('reg.mira')->id,
        ]);

        // Triase KUNING (emergent)
        \App\Models\TriaseIgd::create([
            'kunjungan_id' => $kunjunganIgd->id,
            'kategori' => 'KUNING',
            'waktu_triase' => $kunjunganIgd->tgl_masuk->copy()->addMinutes(3),
            'triase_oleh' => $this->user('pwt.rina')->id,
            'keluhan_utama' => 'Nyeri perut kanan bawah hebat sejak 6 jam, mual muntah, demam.',
            'tanda_vital' => [
                'td_sistol' => 135,
                'td_diastol' => 88,
                'nadi' => 105,
                'respirasi' => 22,
                'suhu' => 38.3,
                'spo2' => 98,
                'gcs' => 'E4M6V5',
            ],
        ]);

        // Order lab cito (CBC + lekosit tinggi)
        $orderLabIgd = $this->buatOrderLabLengkap(
            $kunjunganIgd,
            $dokterIgd,
            [
                'HB' => ['hasil' => '12.8', 'flag' => 'N'],
                'LEKOSIT' => ['hasil' => '17500', 'flag' => 'H'],
                'TROMBOSIT' => ['hasil' => '320000', 'flag' => 'N'],
                'HEMATOKRIT' => ['hasil' => '38', 'flag' => 'N']
            ],
            prioritas: PrioritasOrder::Cito,
            catatan: 'Suspek apendisitis akut',
        );

        // USG Abdomen Bawah
        $orderRadIgd = $this->buatOrderRadiologiLengkap(
            $kunjunganIgd,
            $dokterIgd,
            ['US002'],
            prioritas: PrioritasOrder::Cito,
            klinis: 'Nyeri perut kanan bawah + lekositosis. R/o appendicitis.',
            bacaan: [
                'US002' => [
                    'bacaan' => "Tampak struktur tubular non-kompresibel di regio iliaka kanan diameter 9mm dengan dinding tebal.\nMc Burney sign (+) pada penekanan probe.\nTidak tampak cairan bebas signifikan di Douglas pouch.",
                    'kesan' => 'Gambaran konsisten dengan apendisitis akut',
                    'saran' => 'Konsul bedah untuk pertimbangan operatif',
                    'kritis' => false,
                ],
            ],
        );

        // Diagnosa
        $this->addDiagnosa($kunjunganIgd, 'K35', $dokterIgd, 'PRIMER'); // Acute appendicitis

        // ADMISI RANAP
        $kamar = Kamar::where('status', StatusKamar::Tersedia)->lockForUpdate()->first();
        if (! $kamar) {
            // Fallback: ambil kamar apa saja & set tersedia
            $kamar = Kamar::first();
            $kamar->update(['status' => StatusKamar::Tersedia]);
        }

        // Update kunjungan jadi RI
        $kunjunganIgd->update([
            'tipe' => TipeKunjungan::RawatInap,
            'status' => StatusKunjungan::DalamPemeriksaan,
        ]);

        $ri = RawatInap::create([
            'kunjungan_id' => $kunjunganIgd->id,
            'dpjp_id' => $dpjp->id,
            'tgl_masuk_ri' => $kunjunganIgd->tgl_masuk->copy()->addHours(2),
            'alasan_masuk' => 'Apendisitis akut, rencana apendektomi cito',
        ]);

        KamarInap::create([
            'rawat_inap_id' => $ri->id,
            'kamar_id' => $kamar->id,
            'masuk' => $ri->tgl_masuk_ri,
            'keluar' => now()->subDays(2),
        ]);

        // CPPT — 3 hari
        $this->buatCpptHarian($kunjunganIgd, $dpjp, $ri);

        // Tindakan: apendektomi
        $tindakanApend = $this->cache['tindakan']->first();
        if ($tindakanApend) {
            TindakanKunjungan::create([
                'kunjungan_id' => $kunjunganIgd->id,
                'tindakan_id' => $tindakanApend->id,
                'petugas_id' => $this->user('dr.andika')->id,
                'dokter_id' => $dpjp->id,
                'waktu_tindakan' => $ri->tgl_masuk_ri->copy()->addHours(6),
                'qty' => 1,
                'tarif' => 3500000,
                'subtotal' => 3500000,
                'catatan' => 'Apendektomi laparoskopi. PA: appendicitis flegmonosa. Hemostasis baik.',
            ]);
        }

        // Resep obat selama dirawat (sudah diserahkan)
        $resepIgd = $this->buatResep($kunjunganIgd, $dpjp, [
            ['kode' => 'OBT005', 'jumlah' => 5, 'signa' => '1x1', 'aturan' => 'IV pre-op'],
            ['kode' => 'OBT001', 'jumlah' => 10, 'signa' => '3x1', 'aturan' => 'Prn nyeri'],
        ], status: 'DISERAHKAN');

        // Pulang
        $ri->update([
            'tgl_pulang' => now()->subDays(2),
            'cara_pulang' => 'SEMBUH',
            'resume_medis' => "Pasien wanita 38 tahun masuk dengan keluhan nyeri perut kanan bawah hebat sejak 6 jam SMRS disertai mual, muntah, dan demam. Pemeriksaan fisik: defans muscular McBurney (+), Rovsing (+). Laboratorium: lekositosis 17.500. USG abdomen menunjukkan gambaran apendisitis akut. Pasien menjalani apendektomi laparoskopi pada hari yang sama dengan hasil PA appendicitis flegmonosa. Selama perawatan 3 hari, pasien membaik, demam turun, bising usus kembali normal, mobilisasi baik, dan luka operasi kering. Pasien dipulangkan dalam kondisi stabil dengan instruksi pengobatan lanjutan.",
            'instruksi_pulang' => "1. Obat: cefixime 2x100mg selama 5 hari, paracetamol 3x500mg prn nyeri.\n2. Diet: lunak bertahap ke biasa.\n3. Aktivitas: hindari mengangkat berat 2 minggu.\n4. Luka operasi: jaga kering, ganti perban di puskesmas setiap 2 hari.\n5. Kontrol poli bedah 1 minggu untuk angkat jahitan.\n6. Segera ke IGD jika: demam tinggi, nyeri hebat, luka bernanah, atau muntah persisten.",
            'resume_finalized' => true,
            'resume_finalized_at' => now()->subDays(2),
        ]);

        $kunjunganIgd->update([
            'tgl_keluar' => now()->subDays(2),
            'status' => StatusKunjungan::Selesai,
        ]);

        // Tagihan ranap (BPJS klaim)
        $tagihan = $this->buatTagihan($kunjunganIgd, [
            ['kategori' => 'JASA_DOKTER', 'desc' => 'Visite DPJP (3 hari)', 'qty' => 3, 'harga' => 150000],
            ['kategori' => 'KAMAR', 'desc' => 'Kamar Kelas ' . $kamar->kelas->nama . ' (3 hari)', 'qty' => 3, 'harga' => (float) $kamar->kelas->tarif_per_hari],
            ['kategori' => 'TINDAKAN', 'desc' => 'Apendektomi Laparoskopi', 'qty' => 1, 'harga' => 3500000],
            ['kategori' => 'LABORATORIUM', 'desc' => 'CBC (cito)', 'qty' => 1, 'harga' => 175000],
            ['kategori' => 'RADIOLOGI', 'desc' => 'USG Abdomen Bawah (cito)', 'qty' => 1, 'harga' => 350000],
            ['kategori' => 'OBAT', 'desc' => 'Resep selama dirawat', 'qty' => 1, 'harga' => (float) $resepIgd->total],
        ], finalized: true, status: StatusTagihan::Klaim);

        $this->command->line('  ✓ Skenario 4 — IGD→Ranap: ' . $pasien->nama . ' (apendektomi, ' . $kamar->no_kamar . ', 3 hari)');
    }

    // ==========================================================
    // SKENARIO 5: RJ dengan CICILAN (partial payment)
    // Bp. Robert Hutapea, 55 thn, Poli Bedah, tindakan minor, cicilan 2x
    // ==========================================================
    protected function skenario5_RjCicilan(): void
    {
        $pasien = $this->pasien(4);
        $dokter = $this->dokterByUsername('dr.andika');
        $poli = $this->cache['poli']['UMUM'] ?? $this->cache['poli']->first();

        $kunjungan = Kunjungan::create([
            'no_kunjungan' => $this->genNoKunjungan(),
            'pasien_id' => $pasien->id,
            'tipe' => TipeKunjungan::RawatJalan,
            'penjamin' => Penjamin::Umum,
            'tgl_masuk' => now()->subDays(7)->setTime(14, 0),
            'tgl_keluar' => now()->subDays(7)->setTime(15, 30),
            'status' => StatusKunjungan::Selesai,
            'created_by' => $this->user('reg.mira')->id,
        ]);

        $rj = RawatJalan::create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => $poli->id,
            'dokter_id' => $dokter->id,
            'no_antrian' => 12,
            'waktu_mulai_periksa' => $kunjungan->tgl_masuk->copy()->addMinutes(20),
            'waktu_selesai_periksa' => $kunjungan->tgl_masuk->copy()->addHours(1),
            'tanda_vital' => [
                'td_sistol' => 138,
                'td_diastol' => 88,
                'nadi' => 80,
                'respirasi' => 18,
                'suhu' => 36.8,
                'spo2' => 98,
            ],
            'subjective' => "Benjolan di belakang leher membesar 2 bulan terakhir. Tidak nyeri kecuali ditekan. Tidak demam.",
            'objective' => "Tampak benjolan diameter 3cm region oksipital, lunak, mobile, batas tegas, kulit di atas tidak hiperemis. Tanda-tanda inflamasi (-).",
            'assessment' => "Lipoma regio oksipital",
            'plan' => "Eksisi lipoma dengan anestesi lokal. Edukasi pra dan pasca tindakan.",
            'edukasi' => "Jaga kebersihan luka, kontrol 5 hari untuk angkat jahitan.",
        ]);

        $this->addDiagnosa($kunjungan, 'D17', $dokter, 'PRIMER');

        // Tindakan minor
        $tindakan = $this->cache['tindakan']->skip(1)->first() ?? $this->cache['tindakan']->first();
        if ($tindakan) {
            TindakanKunjungan::create([
                'kunjungan_id' => $kunjungan->id,
                'tindakan_id' => $tindakan->id,
                'petugas_id' => $this->user('dr.andika')->id,
                'dokter_id' => $dokter->id,
                'waktu_tindakan' => $kunjungan->tgl_masuk->copy()->addMinutes(40),
                'qty' => 1,
                'tarif' => 850000,
                'subtotal' => 850000,
                'catatan' => 'Eksisi lipoma 3cm dengan jahitan subkutikuler. PA dikirim.',
            ]);
        }

        $resep = $this->buatResep($kunjungan, $dokter, [
            ['kode' => 'OBT005', 'jumlah' => 5, 'signa' => '1x1', 'aturan' => 'Sesudah makan'],
            ['kode' => 'OBT001', 'jumlah' => 10, 'signa' => '3x1', 'aturan' => 'Prn nyeri'],
        ], status: 'DISERAHKAN');

        $tagihan = $this->buatTagihan($kunjungan, [
            ['kategori' => 'JASA_DOKTER', 'desc' => 'Konsultasi & Tindakan Dokter', 'qty' => 1, 'harga' => 250000],
            ['kategori' => 'TINDAKAN', 'desc' => 'Eksisi Lipoma', 'qty' => 1, 'harga' => 850000],
            ['kategori' => 'OBAT', 'desc' => 'Resep ' . $resep->no_resep, 'qty' => 1, 'harga' => (float) $resep->total],
            ['kategori' => 'ADMINISTRASI', 'desc' => 'Biaya Pendaftaran & Patologi', 'qty' => 1, 'harga' => 50000],
        ], finalized: true);

        // Cicilan 1: 600000 (tunai saat itu)
        $this->buatPembayaran($tagihan, MetodePembayaran::Tunai, 600000, tglOffset: -7);

        // Cicilan 2: 400000 (transfer 3 hari kemudian)
        $remaining = (float) $tagihan->fresh()->sisa;
        if ($remaining > 0) {
            // Bayar sebagian lagi, masih ada sisa
            $this->buatPembayaran($tagihan, MetodePembayaran::Transfer, min(400000, $remaining), tglOffset: -4);
        }

        // Update status jadi cicilan (kalau masih ada sisa)
        if ((float) $tagihan->fresh()->sisa > 0) {
            $tagihan->update(['status' => StatusTagihan::Cicilan]);
        }

        $this->command->line('  ✓ Skenario 5 — RJ Cicilan: ' . $pasien->nama . ' (sisa Rp ' . number_format((float) $tagihan->fresh()->sisa, 0, ',', '.') . ')');
    }

    // ==========================================================
    // HELPERS
    // ==========================================================
    protected function genNoKunjungan(): string
    {
        $prefix = 'KJ/' . now()->format('Y/m');
        $last = Kunjungan::where('no_kunjungan', 'like', "{$prefix}/%")
            ->orderByDesc('no_kunjungan')->lockForUpdate()->first();
        $seq = $last ? ((int) substr($last->no_kunjungan, -5)) + 1 : 1;
        return sprintf('%s/%05d', $prefix, $seq);
    }

    protected function addDiagnosa(Kunjungan $kunjungan, string $icdKode, Dokter $dokter, string $tipe = 'PRIMER'): void
    {
        $icd = $this->cache['icd10'][$icdKode] ?? null;
        if (! $icd) return;

        Diagnosa::create([
            'kunjungan_id' => $kunjungan->id,
            'icd10_kode' => $icd->kode,
            'tipe' => $tipe,
            'dokter_id' => $dokter->id,
        ]);
    }

    protected function buatResep(Kunjungan $kunjungan, Dokter $dokter, array $items, string $status = 'BARU'): Resep
    {
        $resep = Resep::create([
            'no_resep' => 'RX/' . now()->format('Y/m') . '/' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'kunjungan_id' => $kunjungan->id,
            'dokter_id' => $dokter->id,
            'tgl_resep' => $kunjungan->tgl_masuk->copy()->addMinutes(45),
            'status' => $status,
            'apoteker_verifikator_id' => $status !== 'BARU' ? $this->user('apt.fitri')->id : null,
            'verified_at' => $status !== 'BARU' ? $kunjungan->tgl_masuk->copy()->addHour() : null,
            'penyerah_id' => $status === 'DISERAHKAN' ? $this->user('apt.fitri')->id : null,
            'diserahkan_at' => $status === 'DISERAHKAN' ? $kunjungan->tgl_masuk->copy()->addHours(2) : null,
        ]);

        $total = 0;
        foreach ($items as $item) {
            $obat = $this->cache['obat'][$item['kode']] ?? $this->cache['obat']->first();
            $subtotal = (float) $obat->harga_jual * $item['jumlah'];
            $total += $subtotal;

            ResepDetail::create([
                'resep_id' => $resep->id,
                'obat_id' => $obat->id,
                'jumlah' => $item['jumlah'],
                'signa' => $item['signa'],
                'aturan_pakai' => $item['aturan'] ?? null,
                'harga_satuan' => $obat->harga_jual,
                'subtotal' => $subtotal,
                'is_diserahkan' => $status === 'DISERAHKAN',
            ]);
        }

        return $resep->fresh();
    }

    protected function buatOrderLabLengkap(Kunjungan $kunjungan, Dokter $dokter, array $hasilArr, PrioritasOrder $prioritas = PrioritasOrder::Rutin, ?string $catatan = null): OrderLab
    {
        $order = OrderLab::create([
            'no_order' => 'LAB/' . now()->format('Y/m') . '/' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'kunjungan_id' => $kunjungan->id,
            'dokter_id' => $dokter->id,
            'tgl_order' => $kunjungan->tgl_masuk,
            'prioritas' => $prioritas,
            'status' => StatusOrderLab::Selesai,
            'catatan_klinis' => $catatan,
            'sampling_oleh' => $this->user('lab.budi')->id,
            'sampling_at' => $kunjungan->tgl_masuk->copy()->addMinutes(30),
            'validator_id' => $this->user('dr.doni')->id,
            'validated_at' => $kunjungan->tgl_masuk->copy()->addHours(2),
        ]);

        foreach ($hasilArr as $kodeParam => $data) {
            $param = $this->cache['paramlab'][$kodeParam] ?? null;
            if (! $param) continue;

            OrderLabDetail::create([
                'order_id' => $order->id,
                'parameter_id' => $param->id,
                'tarif' => $param->tarif,
            ]);

            HasilLab::create([
                'order_id' => $order->id,
                'parameter_id' => $param->id,
                'hasil' => $data['hasil'],
                'hasil_numerik' => is_numeric($data['hasil']) ? (float) $data['hasil'] : null,
                'satuan' => $param->satuan,
                'nilai_rujukan' => $param->rujukan_normal,
                'flag' => FlagHasilLab::from($data['flag']),
                'input_oleh' => $this->user('lab.budi')->id,
                'validator_id' => $this->user('dr.doni')->id,
                'validated_at' => $order->validated_at,
            ]);
        }

        return $order;
    }

    protected function buatOrderRadiologiLengkap(Kunjungan $kunjungan, Dokter $dokter, array $kodePemeriksaan, PrioritasOrder $prioritas, ?string $klinis, array $bacaan): OrderRadiologi
    {
        $order = OrderRadiologi::create([
            'no_order' => 'RAD/' . now()->format('Y/m') . '/' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'kunjungan_id' => $kunjungan->id,
            'dokter_id' => $dokter->id,
            'tgl_order' => $kunjungan->tgl_masuk,
            'prioritas' => $prioritas,
            'status' => StatusOrderRadiologi::Selesai,
            'klinis' => $klinis,
            'hamil' => false,
            'radiografer_id' => $this->user('rad.tono')->id ?? null,
            'eksekusi_at' => $kunjungan->tgl_masuk->copy()->addMinutes(45),
            'kondisi_teknis' => 'Posisi standar. kV 70, mAs 5. Inspirasi penuh.',
            'radiolog_id' => $this->user('dr.dewi')->id ?? null,
            'validated_at' => $kunjungan->tgl_masuk->copy()->addHours(2),
        ]);

        foreach ($kodePemeriksaan as $kode) {
            $pem = $this->cache['pemrad'][$kode] ?? null;
            if (! $pem) continue;

            OrderRadiologiDetail::create([
                'order_id' => $order->id,
                'pemeriksaan_id' => $pem->id,
                'tarif' => $pem->tarif_kelas3,
            ]);

            $b = $bacaan[$kode] ?? null;
            if ($b) {
                HasilRadiologi::create([
                    'order_id' => $order->id,
                    'pemeriksaan_id' => $pem->id,
                    'bacaan' => $b['bacaan'],
                    'kesan' => $b['kesan'],
                    'saran' => $b['saran'] ?? null,
                    'ada_temuan_kritis' => $b['kritis'] ?? false,
                    'critical_notified' => $b['kritis'] ?? false,
                    'critical_notified_at' => ($b['kritis'] ?? false) ? $order->validated_at : null,
                    'radiolog_id' => $this->user('dr.dewi')->id ?? null,
                    'finalized_at' => $order->validated_at,
                ]);
            }
        }

        return $order;
    }

    protected function buatCpptHarian(Kunjungan $kunjungan, Dokter $dpjp, RawatInap $ri): void
    {
        $start = $ri->tgl_masuk_ri->copy();

        // Day 1 — post-op
        Cppt::create([
            'kunjungan_id' => $kunjungan->id,
            'user_id' => $this->user('dr.andika')->id,
            'profesi' => 'DOKTER',
            'waktu_catatan' => $start->copy()->addHours(8),
            'subjective' => 'Post-op apendektomi. Nyeri luka operasi VAS 4. Belum BAB. Mual minimal.',
            'objective' => 'KU baik. TD 120/80, N 88, S 37.2. Abdomen: BU (+) lemah, luka op kering, tidak ada tanda infeksi.',
            'assessment' => 'Post-op apendektomi H1, hemodinamik stabil',
            'plan' => 'Lanjut antibiotik IV, analgetik prn. Mobilisasi bertahap. Diet cair → lunak.',
        ]);

        // Day 2
        Cppt::create([
            'kunjungan_id' => $kunjungan->id,
            'user_id' => $this->user('dr.andika')->id,
            'profesi' => 'DOKTER',
            'waktu_catatan' => $start->copy()->addDay()->addHours(8),
            'subjective' => 'Nyeri membaik VAS 2. Sudah flatus. Nafsu makan kembali.',
            'objective' => 'KU baik. TD 118/78, N 80, S 36.8. BU normal aktif. Luka kering.',
            'assessment' => 'Post-op H2, perbaikan klinis',
            'plan' => 'Naikkan diet ke biasa. Mobilisasi aktif. Rencana pulang besok jika tidak ada keluhan.',
        ]);

        // Day 3 — pulang
        Cppt::create([
            'kunjungan_id' => $kunjungan->id,
            'user_id' => $this->user('dr.andika')->id,
            'profesi' => 'DOKTER',
            'waktu_catatan' => $start->copy()->addDays(2)->addHours(8),
            'subjective' => 'Tidak ada keluhan. Sudah BAB normal. Aktivitas ringan baik.',
            'objective' => 'KU baik. TTV stabil. Luka op kering, tidak ada tanda infeksi.',
            'assessment' => 'Post-op apendektomi H3, fit for discharge',
            'plan' => 'Pulang dengan obat oral. Kontrol poli bedah 1 minggu.',
        ]);

        // Catatan perawat tiap shift (sample 2)
        Cppt::create([
            'kunjungan_id' => $kunjungan->id,
            'user_id' => $this->user('pwt.yusuf')->id,
            'profesi' => 'PERAWAT',
            'waktu_catatan' => $start->copy()->addHours(14),
            'subjective' => 'Pasien mengeluh nyeri ringan, dapat tidur',
            'objective' => 'TTV: TD 122/82, N 85, S 37.0. Infus lancar. Drain tidak ada.',
            'assessment' => 'Nyeri akut terkontrol',
            'plan' => 'Lanjutkan terapi dokter. Monitor TTV per 4 jam.',
        ]);
    }

    protected function buatTagihan(Kunjungan $kunjungan, array $items, bool $finalized = false, ?StatusTagihan $status = null): Tagihan
    {
        $subtotal = collect($items)->sum(fn($i) => $i['qty'] * $i['harga']);

        $tagihan = Tagihan::create([
            'no_tagihan' => 'INV/' . now()->format('Y/m') . '/' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'kunjungan_id' => $kunjungan->id,
            'tgl_tagihan' => $kunjungan->tgl_keluar ?? now(),
            'subtotal' => $subtotal,
            'diskon' => 0,
            'ppn' => 0,
            'total' => $subtotal,
            'dibayar' => 0,
            'sisa' => $subtotal,
            'status' => $status ?? ($finalized ? StatusTagihan::BelumLunas : StatusTagihan::Draft),
            'finalized_at' => $finalized ? now() : null,
            'finalized_by' => $finalized ? $this->user('kasir.lina')->id : null,
        ]);

        foreach ($items as $i) {
            TagihanDetail::create([
                'tagihan_id' => $tagihan->id,
                'kategori' => $i['kategori'],
                'deskripsi' => $i['desc'],
                'qty' => $i['qty'],
                'harga' => $i['harga'],
                'subtotal' => $i['qty'] * $i['harga'],
            ]);
        }

        return $tagihan->fresh();
    }

    protected function buatPembayaran(Tagihan $tagihan, MetodePembayaran $metode, float $jumlah, int $tglOffset = 0): Pembayaran
    {
        $bayar = Pembayaran::create([
            'no_pembayaran' => 'PAY/' . now()->format('Y/m') . '/' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'tagihan_id' => $tagihan->id,
            'tgl_bayar' => now()->addDays($tglOffset),
            'metode' => $metode,
            'jumlah' => $jumlah,
            'kasir_id' => $this->user('kasir.lina')->id,
            'referensi_eksternal' => in_array($metode->value, ['DEBIT', 'KREDIT', 'QRIS', 'TRANSFER']) ? 'REF' . random_int(100000, 999999) : null,
        ]);

        // Update tagihan
        $dibayar = (float) $tagihan->dibayar + $jumlah;
        $sisa = (float) $tagihan->total - $dibayar;

        $tagihan->update([
            'dibayar' => $dibayar,
            'sisa' => $sisa,
            'status' => $sisa <= 0 ? StatusTagihan::Lunas : StatusTagihan::Cicilan,
        ]);

        return $bayar;
    }

    protected function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 Summary data yang dibuat:');
        $this->command->line('  • ' . Kunjungan::count() . ' kunjungan total');
        $this->command->line('  • ' . RawatJalan::count() . ' RJ, ' . RawatInap::count() . ' RI');
        $this->command->line('  • ' . OrderLab::count() . ' order lab, ' . HasilLab::count() . ' hasil');
        $this->command->line('  • ' . OrderRadiologi::count() . ' order radiologi');
        $this->command->line('  • ' . Resep::count() . ' resep, ' . ResepDetail::count() . ' item obat');
        $this->command->line('  • ' . Diagnosa::count() . ' diagnosa');
        $this->command->line('  • ' . Cppt::count() . ' CPPT');
        $this->command->line('  • ' . Tagihan::count() . ' tagihan, ' . Pembayaran::count() . ' pembayaran');
        $this->command->newLine();
        $this->command->info('💡 Login sebagai admin/dr.andika/lab.budi/apt.fitri/kasir.lina untuk explore.');
    }
}
