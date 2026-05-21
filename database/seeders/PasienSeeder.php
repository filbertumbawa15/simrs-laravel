<?php

namespace Database\Seeders;

use App\Models\Asuransi;
use App\Models\AsuransiPasien;
use App\Models\Pasien;
use App\Models\RekamMedis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasienSeeder extends Seeder
{
    /**
     * Strategy:
     * - 10 pasien "showcase" dengan data fixed → predictable untuk demo & screenshot
     *   beberapa di antaranya punya riwayat penyakit + alergi yang realistic
     * - 40 pasien faker bulk → volume untuk test pagination, search, dst.
     * - Mix umur: dewasa, anak, lansia
     * - ~60% punya asuransi BPJS, ~15% asuransi swasta, sisanya umum
     */
    public function run(): void
    {

        activity()->disableLogging();

        DB::transaction(function () {
            $this->seedShowcasePasien();
            $this->seedBulkPasien();
        });
    }

    /**
     * Pasien fixed untuk demo. Nomor RM dimulai dari 100001.
     */
    protected function seedShowcasePasien(): void
    {
        $showcase = [
            [
                'no_rm' => '100001',
                'nik' => '1271054512750001',
                'nama' => 'Budi Hartono Lubis',
                'tempat_lahir' => 'Medan',
                'tgl_lahir' => '1975-12-05',
                'jenis_kelamin' => 'L',
                'status_pernikahan' => 'KAWIN',
                'agama' => 'Islam',
                'pendidikan' => 'S1',
                'pekerjaan' => 'PNS',
                'alamat' => 'Jl. Sisingamangaraja No. 88',
                'rt' => '03',
                'rw' => '05',
                'kelurahan' => 'Teladan Barat',
                'kecamatan' => 'Medan Kota',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20217',
                'telp' => '081234567801',
                'email' => 'budi.hartono@gmail.com',
                'gol_darah' => 'B+',
                'kontak_darurat_nama' => 'Sari Lubis',
                'kontak_darurat_hubungan' => 'Istri',
                'kontak_darurat_telp' => '081234567802',
                'rm' => [
                    'riwayat_penyakit' => 'Hipertensi sejak 2018, kontrol rutin di Poli Penyakit Dalam',
                    'riwayat_keluarga' => 'Ayah: hipertensi, stroke. Ibu: DM tipe 2',
                    'riwayat_pengobatan' => 'Amlodipine 5mg 1x1, Captopril 25mg 2x1',
                    'alergi_obat' => null,
                    'alergi_makanan' => null,
                    'kebiasaan' => 'Merokok 5 batang/hari (sudah dianjurkan berhenti), kopi 2 cangkir/hari',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567890', 'kelas' => '1'],
            ],
            [
                'no_rm' => '100002',
                'nik' => '1271066308880002',
                'nama' => 'Siti Rahmawati Nasution',
                'tempat_lahir' => 'Binjai',
                'tgl_lahir' => '1988-08-23',
                'jenis_kelamin' => 'P',
                'status_pernikahan' => 'KAWIN',
                'agama' => 'Islam',
                'pendidikan' => 'D3',
                'pekerjaan' => 'Karyawan Swasta',
                'alamat' => 'Jl. Setia Budi No. 142',
                'rt' => '07',
                'rw' => '02',
                'kelurahan' => 'Tanjung Sari',
                'kecamatan' => 'Medan Selayang',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20132',
                'telp' => '085277889900',
                'gol_darah' => 'O+',
                'kontak_darurat_nama' => 'Ahmad Nasution',
                'kontak_darurat_hubungan' => 'Suami',
                'kontak_darurat_telp' => '085277889901',
                'rm' => [
                    'alergi_obat' => 'Penicillin (urtikaria, edema bibir)',
                    'alergi_makanan' => 'Udang',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567891', 'kelas' => '2'],
            ],
            [
                'no_rm' => '100003',
                'nik' => '1271050204600003',
                'nama' => 'H. Soemarno Wijaya',
                'tempat_lahir' => 'Yogyakarta',
                'tgl_lahir' => '1960-04-02',
                'jenis_kelamin' => 'L',
                'status_pernikahan' => 'KAWIN',
                'agama' => 'Islam',
                'pendidikan' => 'S2',
                'pekerjaan' => 'Pensiunan',
                'alamat' => 'Jl. Diponegoro No. 25 Komp. Permata Hijau',
                'rt' => '01',
                'rw' => '01',
                'kelurahan' => 'Polonia',
                'kecamatan' => 'Medan Polonia',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20157',
                'telp' => '081361234567',
                'gol_darah' => 'A+',
                'kontak_darurat_nama' => 'Indah Wijaya',
                'kontak_darurat_hubungan' => 'Anak',
                'kontak_darurat_telp' => '081361234568',
                'rm' => [
                    'riwayat_penyakit' => 'DM tipe 2 (2010), hipertensi (2012), CAD post PCI 2019',
                    'riwayat_keluarga' => 'Ayah meninggal karena stroke usia 68',
                    'riwayat_pengobatan' => 'Metformin 500mg 2x1, Amlodipine 10mg 1x1, Aspilet 80mg 1x1, Simvastatin 20mg 1x1',
                    'kebiasaan' => 'Tidak merokok, olahraga jalan kaki 30 menit/hari',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567892', 'kelas' => '1'],
            ],
            [
                'no_rm' => '100004',
                'nik' => '1271074510200004',
                'nama' => 'Aisyah Putri Tarigan',
                'tempat_lahir' => 'Medan',
                'tgl_lahir' => '2020-05-15',
                'jenis_kelamin' => 'P',
                'status_pernikahan' => 'BELUM_KAWIN',
                'agama' => 'Kristen Protestan',
                'pendidikan' => 'Belum Sekolah',
                'pekerjaan' => 'Pelajar/Mahasiswa',
                'alamat' => 'Jl. Jamin Ginting No. 78',
                'rt' => '05',
                'rw' => '03',
                'kelurahan' => 'Padang Bulan',
                'kecamatan' => 'Medan Baru',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20155',
                'telp' => '081298765432',
                'gol_darah' => 'B+',
                'nama_ayah' => 'Rendy Tarigan',
                'nama_ibu' => 'Maria Sembiring',
                'kontak_darurat_nama' => 'Maria Sembiring',
                'kontak_darurat_hubungan' => 'Orang Tua',
                'kontak_darurat_telp' => '081298765432',
                'rm' => [
                    'riwayat_penyakit' => 'Riwayat ISPA berulang, asma episodik',
                    'alergi_obat' => null,
                    'alergi_makanan' => 'Telur (gatal-gatal)',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567893', 'kelas' => '2'],
            ],
            [
                'no_rm' => '100005',
                'nik' => '1271082008950005',
                'nama' => 'dr. Andi Permana, M.Sc',
                'tempat_lahir' => 'Padang',
                'tgl_lahir' => '1995-08-20',
                'jenis_kelamin' => 'L',
                'status_pernikahan' => 'BELUM_KAWIN',
                'agama' => 'Islam',
                'pendidikan' => 'S2',
                'pekerjaan' => 'Dokter',
                'alamat' => 'Apartemen Cambridge Tower B Lt. 12 No. 1208',
                'kelurahan' => 'Petisah Tengah',
                'kecamatan' => 'Medan Petisah',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20114',
                'telp' => '081287654321',
                'email' => 'dr.andi.p@gmail.com',
                'gol_darah' => 'AB+',
                'kontak_darurat_nama' => 'Rini Permana',
                'kontak_darurat_hubungan' => 'Orang Tua',
                'kontak_darurat_telp' => '081287654300',
                'rm' => [
                    'kebiasaan' => 'Olahraga rutin gym 3x/minggu',
                ],
                'asuransi' => ['kode' => 'ALL', 'no_polis' => 'ALL-202401-887766', 'pemegang' => 'Andi Permana'],
            ],
            [
                'no_rm' => '100006',
                'nik' => '1208114702800006',
                'nama' => 'Rina Maharani Saputri',
                'tempat_lahir' => 'Lubuk Pakam',
                'tgl_lahir' => '1980-02-07',
                'jenis_kelamin' => 'P',
                'status_pernikahan' => 'KAWIN',
                'agama' => 'Islam',
                'pendidikan' => 'SMA',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'alamat' => 'Jl. Pelita IV No. 56',
                'rt' => '04',
                'rw' => '06',
                'kelurahan' => 'Lubuk Pakam I-II',
                'kecamatan' => 'Lubuk Pakam',
                'kabupaten' => 'Deli Serdang',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20514',
                'telp' => '082277665544',
                'gol_darah' => 'A+',
                'kontak_darurat_nama' => 'Eko Saputra',
                'kontak_darurat_hubungan' => 'Suami',
                'kontak_darurat_telp' => '082277665500',
                'rm' => [
                    'riwayat_penyakit' => 'G3P2A0, hamil 32 minggu saat ini',
                    'riwayat_keluarga' => 'Ibu DM tipe 2',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567894', 'kelas' => '3'],
            ],
            [
                'no_rm' => '100007',
                'nik' => '1271093110450007',
                'nama' => 'Drs. Bambang Sutrisno',
                'tempat_lahir' => 'Surabaya',
                'tgl_lahir' => '1945-10-31',
                'jenis_kelamin' => 'L',
                'status_pernikahan' => 'CERAI_MATI',
                'agama' => 'Katolik',
                'pendidikan' => 'S1',
                'pekerjaan' => 'Pensiunan',
                'alamat' => 'Jl. AR Hakim No. 12',
                'rt' => '02',
                'rw' => '04',
                'kelurahan' => 'Sei Rengas I',
                'kecamatan' => 'Medan Kota',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20212',
                'telp' => '081365432100',
                'gol_darah' => 'O-',
                'kontak_darurat_nama' => 'Theresia Sutrisno',
                'kontak_darurat_hubungan' => 'Anak',
                'kontak_darurat_telp' => '081365432101',
                'rm' => [
                    'riwayat_penyakit' => 'PPOK, hipertensi grade 2, BPH (sudah TURP 2018)',
                    'riwayat_pengobatan' => 'Salbutamol inhaler PRN, Amlodipine 5mg 1x1, Tamsulosin 0.4mg 1x1',
                    'alergi_obat' => 'Sulfa (rash)',
                    'kebiasaan' => 'Eks-perokok berat (40 tahun, berhenti 2015)',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567895', 'kelas' => '1'],
            ],
            [
                'no_rm' => '100008',
                'nik' => '1271101205930008',
                'nama' => 'Iqbal Maulana Harahap',
                'tempat_lahir' => 'Medan',
                'tgl_lahir' => '1993-05-12',
                'jenis_kelamin' => 'L',
                'status_pernikahan' => 'KAWIN',
                'agama' => 'Islam',
                'pendidikan' => 'S1',
                'pekerjaan' => 'Wiraswasta',
                'alamat' => 'Komplek Cemara Asri Blok F No. 23',
                'rt' => '08',
                'rw' => '02',
                'kelurahan' => 'Sampali',
                'kecamatan' => 'Medan Tembung',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20371',
                'telp' => '081299887766',
                'email' => 'iqbal.maulana93@gmail.com',
                'gol_darah' => 'B+',
                'kontak_darurat_nama' => 'Lia Harahap',
                'kontak_darurat_hubungan' => 'Istri',
                'kontak_darurat_telp' => '081299887700',
                'rm' => [
                    'kebiasaan' => 'Tidak merokok',
                ],
                'asuransi' => null, // umum
            ],
            [
                'no_rm' => '100009',
                'nik' => '1212055708720009',
                'nama' => 'Dewi Anggraini Sinaga',
                'tempat_lahir' => 'Stabat',
                'tgl_lahir' => '1972-08-17',
                'jenis_kelamin' => 'P',
                'status_pernikahan' => 'KAWIN',
                'agama' => 'Kristen Protestan',
                'pendidikan' => 'SMA',
                'pekerjaan' => 'Petani',
                'alamat' => 'Dusun III Desa Stabat Lama',
                'kelurahan' => 'Stabat Lama',
                'kecamatan' => 'Wampu',
                'kabupaten' => 'Langkat',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20851',
                'telp' => '082166554433',
                'gol_darah' => 'A+',
                'kontak_darurat_nama' => 'Pardomuan Sinaga',
                'kontak_darurat_hubungan' => 'Suami',
                'kontak_darurat_telp' => '082166554400',
                'rm' => [
                    'riwayat_penyakit' => 'DM tipe 2 (terkontrol kurang baik), HbA1c terakhir 8.9%',
                    'riwayat_pengobatan' => 'Metformin 500mg 2x1',
                ],
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567896', 'kelas' => '3'],
            ],
            [
                'no_rm' => '100010',
                'nik' => '1271111003001010',
                'nama' => 'Reza Akbar Manurung',
                'tempat_lahir' => 'Medan',
                'tgl_lahir' => '2000-03-10',
                'jenis_kelamin' => 'L',
                'status_pernikahan' => 'BELUM_KAWIN',
                'agama' => 'Kristen Protestan',
                'pendidikan' => 'S1',
                'pekerjaan' => 'Pelajar/Mahasiswa',
                'alamat' => 'Jl. Letda Sujono Gg. Sepakat No. 7',
                'rt' => '06',
                'rw' => '03',
                'kelurahan' => 'Bandar Selamat',
                'kecamatan' => 'Medan Tembung',
                'kabupaten' => 'Medan',
                'provinsi' => 'Sumatera Utara',
                'kode_pos' => '20371',
                'telp' => '085260123456',
                'email' => 'reza.akbar@student.usu.ac.id',
                'gol_darah' => 'O+',
                'nama_ayah' => 'Daniel Manurung',
                'nama_ibu' => 'Ester Pakpahan',
                'kontak_darurat_nama' => 'Ester Pakpahan',
                'kontak_darurat_hubungan' => 'Orang Tua',
                'kontak_darurat_telp' => '085260123400',
                'rm' => null,
                'asuransi' => ['kode' => 'BPJS', 'no_polis' => '0001234567897', 'kelas' => '3'],
            ],
        ];

        $asuransiMap = Asuransi::pluck('id', 'kode');

        foreach ($showcase as $data) {
            $rm = $data['rm'];
            $asr = $data['asuransi'];
            unset($data['rm'], $data['asuransi']);

            $pasien = Pasien::firstOrCreate(
                ['no_rm' => $data['no_rm']],
                $data
            );

            // Rekam medis selalu dibuat — kosong jika belum ada riwayat
            RekamMedis::firstOrCreate(
                ['pasien_id' => $pasien->id],
                $rm ?? []
            );

            // Asuransi (jika ada)
            if ($asr && isset($asuransiMap[$asr['kode']])) {
                AsuransiPasien::firstOrCreate(
                    [
                        'pasien_id' => $pasien->id,
                        'asuransi_id' => $asuransiMap[$asr['kode']],
                        'no_polis' => $asr['no_polis'],
                    ],
                    [
                        'nama_pemegang' => $asr['pemegang'] ?? $pasien->nama,
                        'valid_from' => now()->startOfYear(),
                        'valid_until' => now()->endOfYear()->addYear(),
                        'kelas_hak' => $asr['kelas'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('  Showcase pasien: ' . count($showcase) . ' record');
    }

    /**
     * Bulk pasien via factory — untuk testing pagination, search, dst.
     */
    protected function seedBulkPasien(): void
    {
        $bpjsId = Asuransi::where('kode', 'BPJS')->value('id');
        $asuransiSwasta = Asuransi::where('tipe', 'SWASTA')->pluck('id')->all();

        // Tentukan no_rm awal dari yang sudah ada
        $lastNoRm = (int) (Pasien::max('no_rm') ?? 100000);

        $bulkCount = 40;
        $batches = [
            // Mix umur
            ['count' => 25, 'state' => null],             // dewasa
            ['count' => 8,  'state' => 'anak'],
            ['count' => 7,  'state' => 'lansia'],
        ];

        $created = 0;
        foreach ($batches as $batch) {
            $factory = Pasien::factory()->count($batch['count']);
            if ($batch['state']) {
                $factory = $factory->{$batch['state']}();
            }

            $pasienList = $factory->make()->each(function ($pasien) use (&$lastNoRm) {
                $lastNoRm++;
                $pasien->no_rm = str_pad((string) $lastNoRm, 6, '0', STR_PAD_LEFT);
            });

            foreach ($pasienList as $p) {
                $p->save();
                $created++;

                // Rekam medis kosong (default — pasien baru belum ada riwayat)
                RekamMedis::create(['pasien_id' => $p->id]);

                // Random asuransi: 60% BPJS, 15% swasta, 25% umum
                $r = mt_rand(1, 100);
                if ($r <= 60 && $bpjsId) {
                    AsuransiPasien::create([
                        'pasien_id' => $p->id,
                        'asuransi_id' => $bpjsId,
                        'no_polis' => '00012' . str_pad((string) (mt_rand(10000000, 99999999)), 8, '0', STR_PAD_LEFT),
                        'nama_pemegang' => $p->nama,
                        'valid_from' => now()->startOfYear(),
                        'valid_until' => now()->endOfYear()->addYear(),
                        'kelas_hak' => (string) mt_rand(1, 3),
                        'is_active' => true,
                    ]);
                } elseif ($r <= 75 && ! empty($asuransiSwasta)) {
                    AsuransiPasien::create([
                        'pasien_id' => $p->id,
                        'asuransi_id' => $asuransiSwasta[array_rand($asuransiSwasta)],
                        'no_polis' => 'POL-' . now()->format('Y') . '-' . str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
                        'nama_pemegang' => $p->nama,
                        'valid_from' => now()->subMonths(mt_rand(1, 12))->startOfMonth(),
                        'valid_until' => now()->addMonths(mt_rand(6, 24))->endOfMonth(),
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info("  Bulk pasien: {$created} record");
    }
}
