<?php

namespace Database\Seeders;

use App\Models\Asuransi;
use App\Models\Dokter;
use App\Models\Icd10;
use App\Models\JadwalDokter;
use App\Models\Kamar;
use App\Models\KelasKamar;
use App\Models\Obat;
use App\Models\ParameterLab;
use App\Models\Poli;
use App\Models\StokObat;
use App\Models\Tindakan;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPoli();
        $this->seedDokter();
        $this->seedJadwal();
        $this->seedKelasKamar();
        $this->seedKamar();
        $this->seedIcd10();
        $this->seedAsuransi();
        $this->seedObat();
        $this->seedParameterLab();
        $this->seedTindakan();
    }

    protected function seedPoli(): void
    {
        $data = [
            ['kode' => 'UMUM', 'nama' => 'Poli Umum', 'lokasi' => 'Lt. 1 Gedung A'],
            ['kode' => 'ANAK', 'nama' => 'Poli Anak', 'lokasi' => 'Lt. 1 Gedung A'],
            ['kode' => 'PD', 'nama' => 'Poli Penyakit Dalam', 'lokasi' => 'Lt. 2 Gedung A'],
            ['kode' => 'OBG', 'nama' => 'Poli Obstetri & Ginekologi', 'lokasi' => 'Lt. 2 Gedung A'],
            ['kode' => 'BDH', 'nama' => 'Poli Bedah Umum', 'lokasi' => 'Lt. 2 Gedung B'],
            ['kode' => 'SRF', 'nama' => 'Poli Saraf', 'lokasi' => 'Lt. 3 Gedung B'],
            ['kode' => 'MTA', 'nama' => 'Poli Mata', 'lokasi' => 'Lt. 3 Gedung B'],
            ['kode' => 'GGI', 'nama' => 'Poli Gigi', 'lokasi' => 'Lt. 1 Gedung B'],
        ];

        foreach ($data as $d) {
            Poli::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }

    protected function seedDokter(): void
    {
        $data = [
            ['kode' => 'D0001', 'sip' => 'SIP/2019/0123', 'nama' => 'Andika Pratama', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.PD', 'spesialisasi' => 'Penyakit Dalam', 'jasa_konsul' => 250000],
            ['kode' => 'D0002', 'sip' => 'SIP/2018/0456', 'nama' => 'Sari Wulandari', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.A', 'spesialisasi' => 'Anak', 'jasa_konsul' => 200000],
            ['kode' => 'D0003', 'sip' => 'SIP/2020/0789', 'nama' => 'Bayu Setiawan', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.OG', 'spesialisasi' => 'Obstetri & Ginekologi', 'jasa_konsul' => 300000],
            ['kode' => 'D0004', 'sip' => 'SIP/2017/0234', 'nama' => 'Hendra Lubis', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.B', 'spesialisasi' => 'Bedah Umum', 'jasa_konsul' => 350000],
            ['kode' => 'D0005', 'sip' => 'SIP/2021/0567', 'nama' => 'Maya Sari', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.S', 'spesialisasi' => 'Saraf', 'jasa_konsul' => 250000],
            ['kode' => 'D0006', 'sip' => 'SIP/2019/0890', 'nama' => 'Reza Hidayat', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.M', 'spesialisasi' => 'Mata', 'jasa_konsul' => 225000],
            ['kode' => 'D0007', 'sip' => 'SIP/2022/0345', 'nama' => 'Putri Anggraini', 'gelar_depan' => 'drg.', 'spesialisasi' => 'Gigi Umum', 'jasa_konsul' => 175000],
            ['kode' => 'D0008', 'sip' => 'SIP/2020/0678', 'nama' => 'Iqbal Nasution', 'gelar_depan' => 'dr.', 'spesialisasi' => 'Umum', 'jasa_konsul' => 100000],
            ['kode' => 'D0009', 'sip' => 'SIP/2018/0901', 'nama' => 'Hanna Manurung', 'gelar_depan' => 'dr.', 'spesialisasi' => 'Umum (IGD)', 'jasa_konsul' => 125000],
            ['kode' => 'D0010', 'sip' => 'SIP/2023/0112', 'nama' => 'Doni Saputra', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.PK', 'spesialisasi' => 'Patologi Klinik', 'jasa_konsul' => 0],
        ];

        foreach ($data as $d) {
            Dokter::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }

    protected function seedJadwal(): void
    {
        $mapping = [
            'D0001' => [['PD', 'SENIN', '08:00', '12:00'], ['PD', 'RABU', '08:00', '12:00'], ['PD', 'JUMAT', '14:00', '17:00']],
            'D0002' => [['ANAK', 'SENIN', '09:00', '13:00'], ['ANAK', 'SELASA', '09:00', '13:00'], ['ANAK', 'KAMIS', '09:00', '13:00']],
            'D0003' => [['OBG', 'SENIN', '10:00', '14:00'], ['OBG', 'RABU', '10:00', '14:00']],
            'D0004' => [['BDH', 'SELASA', '08:00', '12:00'], ['BDH', 'KAMIS', '08:00', '12:00']],
            'D0008' => [['UMUM', 'SENIN', '07:00', '14:00'], ['UMUM', 'SELASA', '07:00', '14:00'], ['UMUM', 'RABU', '07:00', '14:00'], ['UMUM', 'KAMIS', '07:00', '14:00'], ['UMUM', 'JUMAT', '07:00', '14:00']],
        ];

        foreach ($mapping as $dokterKode => $jadwals) {
            $dokter = Dokter::where('kode', $dokterKode)->first();
            if (! $dokter) {
                continue;
            }

            foreach ($jadwals as [$poliKode, $hari, $mulai, $selesai]) {
                $poli = Poli::where('kode', $poliKode)->first();
                if (! $poli) {
                    continue;
                }

                JadwalDokter::firstOrCreate([
                    'dokter_id' => $dokter->id,
                    'poli_id' => $poli->id,
                    'hari' => $hari,
                    'jam_mulai' => $mulai,
                ], [
                    'jam_selesai' => $selesai,
                    'kuota' => 25,
                ]);
            }
        }
    }

    protected function seedKelasKamar(): void
    {
        $data = [
            ['kode' => 'VIP', 'nama' => 'VIP', 'tarif_per_hari' => 850000, 'urutan' => 1],
            ['kode' => 'I', 'nama' => 'I', 'tarif_per_hari' => 550000, 'urutan' => 2],
            ['kode' => 'II', 'nama' => 'II', 'tarif_per_hari' => 350000, 'urutan' => 3],
            ['kode' => 'III', 'nama' => 'III', 'tarif_per_hari' => 200000, 'urutan' => 4],
        ];

        foreach ($data as $d) {
            KelasKamar::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }

    protected function seedKamar(): void
    {
        $kelas = [
            'VIP' => 8,
            'I' => 15,
            'II' => 25,
            'III' => 40,
        ];

        foreach ($kelas as $kode => $jumlah) {
            $kelasModel = KelasKamar::where('kode', $kode)->first();
            for ($i = 1; $i <= $jumlah; $i++) {
                Kamar::firstOrCreate(
                    ['no_kamar' => sprintf('%s-%02d', $kode, $i)],
                    [
                        'kelas_id' => $kelasModel->id,
                        'status' => 'TERSEDIA',
                        'lokasi' => 'Lt. ' . rand(2, 5) . ' Gedung ' . (in_array($kode, ['VIP', 'I']) ? 'A' : 'C'),
                        'kapasitas' => 1,
                    ]
                );
            }
        }
    }

    protected function seedIcd10(): void
    {
        $data = [
            ['kode' => 'A09', 'nama' => 'Diare dan gastroenteritis akibat infeksi', 'kategori' => 'Penyakit Infeksi'],
            ['kode' => 'A91', 'nama' => 'Demam berdarah dengue', 'kategori' => 'Penyakit Infeksi'],
            ['kode' => 'B34.9', 'nama' => 'Infeksi virus tidak spesifik', 'kategori' => 'Penyakit Infeksi'],
            ['kode' => 'E11.9', 'nama' => 'Diabetes mellitus tipe 2 tanpa komplikasi', 'kategori' => 'Endokrin'],
            ['kode' => 'E78.0', 'nama' => 'Hiperkolesterolemia murni', 'kategori' => 'Endokrin'],
            ['kode' => 'I10', 'nama' => 'Hipertensi esensial (primer)', 'kategori' => 'Kardiovaskular'],
            ['kode' => 'I25.9', 'nama' => 'Penyakit jantung iskemik kronik', 'kategori' => 'Kardiovaskular'],
            ['kode' => 'J00', 'nama' => 'Nasofaringitis akut (common cold)', 'kategori' => 'Pernapasan'],
            ['kode' => 'J06.9', 'nama' => 'ISPA atas tidak spesifik', 'kategori' => 'Pernapasan'],
            ['kode' => 'J18.9', 'nama' => 'Pneumonia tidak spesifik', 'kategori' => 'Pernapasan'],
            ['kode' => 'J45.9', 'nama' => 'Asma tidak spesifik', 'kategori' => 'Pernapasan'],
            ['kode' => 'K29.7', 'nama' => 'Gastritis tidak spesifik', 'kategori' => 'Saluran Cerna'],
            ['kode' => 'K35.8', 'nama' => 'Apendisitis akut tidak spesifik', 'kategori' => 'Saluran Cerna'],
            ['kode' => 'K59.0', 'nama' => 'Konstipasi', 'kategori' => 'Saluran Cerna'],
            ['kode' => 'M54.5', 'nama' => 'Nyeri punggung bawah', 'kategori' => 'Muskuloskeletal'],
            ['kode' => 'N39.0', 'nama' => 'Infeksi saluran kemih', 'kategori' => 'Genitourinaria'],
            ['kode' => 'O80', 'nama' => 'Persalinan tunggal spontan', 'kategori' => 'Obstetri'],
            ['kode' => 'R50.9', 'nama' => 'Demam tidak spesifik', 'kategori' => 'Gejala Umum'],
            ['kode' => 'R51', 'nama' => 'Sakit kepala', 'kategori' => 'Gejala Umum'],
            ['kode' => 'Z00.0', 'nama' => 'Pemeriksaan kesehatan umum', 'kategori' => 'Faktor Lain'],
        ];

        foreach ($data as $d) {
            Icd10::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }

    protected function seedAsuransi(): void
    {
        Asuransi::firstOrCreate(['kode' => 'BPJS'], [
            'nama' => 'BPJS Kesehatan',
            'tipe' => 'BPJS',
            'is_active' => true,
        ]);

        $swasta = [
            ['kode' => 'ALL', 'nama' => 'Allianz', 'tipe' => 'SWASTA'],
            ['kode' => 'AXA', 'nama' => 'AXA Mandiri', 'tipe' => 'SWASTA'],
            ['kode' => 'PRU', 'nama' => 'Prudential', 'tipe' => 'SWASTA'],
            ['kode' => 'INH', 'nama' => 'In-Health', 'tipe' => 'SWASTA'],
        ];
        foreach ($swasta as $d) {
            Asuransi::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }

    protected function seedObat(): void
    {
        $data = [
            ['kode' => 'OB0001', 'nama' => 'Paracetamol 500mg tab', 'golongan' => 'BEBAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '500mg', 'harga_jual' => 500, 'stok_minimum' => 100, 'is_fornas' => true],
            ['kode' => 'OB0002', 'nama' => 'Amoxicillin 500mg kaps', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Kapsul', 'satuan' => 'Kapsul', 'kekuatan' => '500mg', 'harga_jual' => 1500, 'stok_minimum' => 50, 'is_fornas' => true],
            ['kode' => 'OB0003', 'nama' => 'Ibuprofen 400mg tab', 'golongan' => 'BEBAS_TERBATAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '400mg', 'harga_jual' => 800, 'stok_minimum' => 100],
            ['kode' => 'OB0004', 'nama' => 'Omeprazole 20mg kaps', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Kapsul', 'satuan' => 'Kapsul', 'kekuatan' => '20mg', 'harga_jual' => 2500, 'stok_minimum' => 50],
            ['kode' => 'OB0005', 'nama' => 'Cefixime 100mg kaps', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Kapsul', 'satuan' => 'Kapsul', 'kekuatan' => '100mg', 'harga_jual' => 3500, 'stok_minimum' => 50],
            ['kode' => 'OB0006', 'nama' => 'Metformin 500mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '500mg', 'harga_jual' => 600, 'stok_minimum' => 100, 'is_fornas' => true],
            ['kode' => 'OB0007', 'nama' => 'Amlodipine 5mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '5mg', 'harga_jual' => 900, 'stok_minimum' => 100, 'is_fornas' => true],
            ['kode' => 'OB0008', 'nama' => 'Simvastatin 20mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '20mg', 'harga_jual' => 1200, 'stok_minimum' => 50],
            ['kode' => 'OB0009', 'nama' => 'Salbutamol 2mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '2mg', 'harga_jual' => 700, 'stok_minimum' => 50],
            ['kode' => 'OB0010', 'nama' => 'Ranitidine 150mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '150mg', 'harga_jual' => 800, 'stok_minimum' => 50],
            ['kode' => 'OB0015', 'nama' => 'Captopril 25mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '25mg', 'harga_jual' => 500, 'stok_minimum' => 100, 'is_fornas' => true],
            ['kode' => 'OB0016', 'nama' => 'Asam Mefenamat 500mg', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Kapsul', 'satuan' => 'Kapsul', 'kekuatan' => '500mg', 'harga_jual' => 700, 'stok_minimum' => 100],
            ['kode' => 'OB0017', 'nama' => 'Cetirizine 10mg tab', 'golongan' => 'KERAS', 'bentuk_sediaan' => 'Tablet', 'satuan' => 'Tablet', 'kekuatan' => '10mg', 'harga_jual' => 1100, 'stok_minimum' => 50],
        ];

        foreach ($data as $d) {
            $obat = Obat::firstOrCreate(['kode' => $d['kode']], $d);

            // Buat 1 batch stok awal yang ample
            StokObat::firstOrCreate(
                ['obat_id' => $obat->id, 'no_batch' => 'B' . now()->format('Ymd') . '001'],
                [
                    'jumlah_masuk' => 500,
                    'jumlah_sisa' => 500,
                    'tgl_masuk' => now()->subDays(30),
                    'exp_date' => now()->addMonths(18),
                    'hpp' => $d['harga_jual'] * 0.65,
                    'supplier' => 'PT Kimia Farma Trading & Distribution',
                ]
            );
        }
    }

    protected function seedParameterLab(): void
    {
        $data = [
            ['kode' => 'HB', 'nama' => 'Hemoglobin', 'kategori' => 'Hematologi', 'satuan' => 'g/dL', 'rujukan_normal' => '13.0-17.0 (P), 12.0-15.0 (W)', 'nilai_rujukan_min' => 12, 'nilai_rujukan_max' => 17, 'nilai_kritis_low' => 7, 'nilai_kritis_high' => 20, 'tarif' => 25000, 'loinc_code' => '718-7'],
            ['kode' => 'WBC', 'nama' => 'Leukosit', 'kategori' => 'Hematologi', 'satuan' => '10^3/uL', 'rujukan_normal' => '4.5-11.0', 'nilai_rujukan_min' => 4.5, 'nilai_rujukan_max' => 11, 'nilai_kritis_low' => 2, 'nilai_kritis_high' => 30, 'tarif' => 25000, 'loinc_code' => '6690-2'],
            ['kode' => 'PLT', 'nama' => 'Trombosit', 'kategori' => 'Hematologi', 'satuan' => '10^3/uL', 'rujukan_normal' => '150-450', 'nilai_rujukan_min' => 150, 'nilai_rujukan_max' => 450, 'nilai_kritis_low' => 50, 'nilai_kritis_high' => 1000, 'tarif' => 25000, 'loinc_code' => '777-3'],
            ['kode' => 'HCT', 'nama' => 'Hematokrit', 'kategori' => 'Hematologi', 'satuan' => '%', 'rujukan_normal' => '40-54 (P), 37-47 (W)', 'nilai_rujukan_min' => 37, 'nilai_rujukan_max' => 54, 'tarif' => 25000],
            ['kode' => 'GDS', 'nama' => 'Glukosa Darah Sewaktu', 'kategori' => 'Kimia Klinik', 'satuan' => 'mg/dL', 'rujukan_normal' => '70-200', 'nilai_rujukan_min' => 70, 'nilai_rujukan_max' => 200, 'nilai_kritis_low' => 40, 'nilai_kritis_high' => 400, 'tarif' => 30000, 'loinc_code' => '2345-7'],
            ['kode' => 'GDP', 'nama' => 'Glukosa Darah Puasa', 'kategori' => 'Kimia Klinik', 'satuan' => 'mg/dL', 'rujukan_normal' => '70-100', 'nilai_rujukan_min' => 70, 'nilai_rujukan_max' => 100, 'tarif' => 30000],
            ['kode' => 'CHOL', 'nama' => 'Kolesterol Total', 'kategori' => 'Kimia Klinik', 'satuan' => 'mg/dL', 'rujukan_normal' => '<200', 'nilai_rujukan_max' => 200, 'tarif' => 45000, 'loinc_code' => '2093-3'],
            ['kode' => 'CREA', 'nama' => 'Kreatinin', 'kategori' => 'Kimia Klinik', 'satuan' => 'mg/dL', 'rujukan_normal' => '0.7-1.3 (P), 0.6-1.1 (W)', 'nilai_rujukan_min' => 0.6, 'nilai_rujukan_max' => 1.3, 'tarif' => 35000],
            ['kode' => 'UREA', 'nama' => 'Ureum', 'kategori' => 'Kimia Klinik', 'satuan' => 'mg/dL', 'rujukan_normal' => '15-45', 'nilai_rujukan_min' => 15, 'nilai_rujukan_max' => 45, 'tarif' => 35000],
            ['kode' => 'SGOT', 'nama' => 'SGOT (AST)', 'kategori' => 'Kimia Klinik', 'satuan' => 'U/L', 'rujukan_normal' => '<35', 'nilai_rujukan_max' => 35, 'tarif' => 40000],
            ['kode' => 'SGPT', 'nama' => 'SGPT (ALT)', 'kategori' => 'Kimia Klinik', 'satuan' => 'U/L', 'rujukan_normal' => '<40', 'nilai_rujukan_max' => 40, 'tarif' => 40000],
            ['kode' => 'URINA', 'nama' => 'Urinalisis Lengkap', 'kategori' => 'Urinalisis', 'tipe_hasil' => 'KUALITATIF', 'rujukan_normal' => 'Normal', 'tarif' => 35000],
        ];

        foreach ($data as $d) {
            ParameterLab::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }

    protected function seedTindakan(): void
    {
        $data = [
            ['kode' => 'TD001', 'nama' => 'Pemeriksaan Umum RJ', 'kategori' => 'Konsultasi', 'tarif_vip' => 100000, 'tarif_kelas1' => 100000, 'tarif_kelas2' => 100000, 'tarif_kelas3' => 100000],
            ['kode' => 'TD003', 'nama' => 'Injeksi IM/IV', 'kategori' => 'Tindakan Perawat', 'tarif_vip' => 25000, 'tarif_kelas1' => 25000, 'tarif_kelas2' => 25000, 'tarif_kelas3' => 25000],
            ['kode' => 'TD004', 'nama' => 'Infus', 'kategori' => 'Tindakan Perawat', 'tarif_vip' => 75000, 'tarif_kelas1' => 60000, 'tarif_kelas2' => 50000, 'tarif_kelas3' => 40000],
            ['kode' => 'TD005', 'nama' => 'Nebulizer', 'kategori' => 'Tindakan', 'tarif_vip' => 100000, 'tarif_kelas1' => 85000, 'tarif_kelas2' => 70000, 'tarif_kelas3' => 60000],
            ['kode' => 'TD006', 'nama' => 'EKG', 'kategori' => 'Penunjang', 'tarif_vip' => 125000, 'tarif_kelas1' => 110000, 'tarif_kelas2' => 95000, 'tarif_kelas3' => 80000],
            ['kode' => 'TD009', 'nama' => 'Jahit Luka', 'kategori' => 'Bedah Minor', 'tarif_vip' => 250000, 'tarif_kelas1' => 200000, 'tarif_kelas2' => 175000, 'tarif_kelas3' => 150000],
            ['kode' => 'TD010', 'nama' => 'Visite Dokter Spesialis (RI)', 'kategori' => 'Visite', 'tarif_vip' => 200000, 'tarif_kelas1' => 150000, 'tarif_kelas2' => 100000, 'tarif_kelas3' => 75000],
        ];

        foreach ($data as $d) {
            Tindakan::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }
}
