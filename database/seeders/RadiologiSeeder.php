<?php

namespace Database\Seeders;

use App\Models\PemeriksaanRadiologi;
use Illuminate\Database\Seeder;

class RadiologiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // X-Ray
            ['kode' => 'XR001', 'nama' => 'Thorax PA', 'modalitas' => 'XRAY', 'region' => 'Thorax', 't' => [150000, 130000, 110000, 95000], 'template' => "Cor: ukuran dan bentuk dalam batas normal.\nPulmo: tidak tampak infiltrat, nodul, atau kavitas.\nDiafragma dan sinus costofrenicus normal.\nTulang-tulang costae intact."],
            ['kode' => 'XR002', 'nama' => 'Thorax AP', 'modalitas' => 'XRAY', 'region' => 'Thorax', 't' => [150000, 130000, 110000, 95000]],
            ['kode' => 'XR003', 'nama' => 'Cranium AP/Lateral', 'modalitas' => 'XRAY', 'region' => 'Kepala', 't' => [200000, 175000, 150000, 130000]],
            ['kode' => 'XR004', 'nama' => 'Abdomen 3 Posisi', 'modalitas' => 'XRAY', 'region' => 'Abdomen', 't' => [250000, 220000, 190000, 165000]],
            ['kode' => 'XR005', 'nama' => 'BNO (Abdomen Polos)', 'modalitas' => 'XRAY', 'region' => 'Abdomen', 't' => [175000, 150000, 130000, 110000]],
            ['kode' => 'XR006', 'nama' => 'Genu AP/Lateral', 'modalitas' => 'XRAY', 'region' => 'Ekstremitas', 't' => [175000, 150000, 130000, 110000]],
            ['kode' => 'XR007', 'nama' => 'Manus AP/Oblique', 'modalitas' => 'XRAY', 'region' => 'Ekstremitas', 't' => [150000, 130000, 110000, 95000]],
            ['kode' => 'XR008', 'nama' => 'Pedis AP/Oblique', 'modalitas' => 'XRAY', 'region' => 'Ekstremitas', 't' => [150000, 130000, 110000, 95000]],
            ['kode' => 'XR009', 'nama' => 'Lumbosacral AP/Lateral', 'modalitas' => 'XRAY', 'region' => 'Vertebra', 't' => [225000, 200000, 175000, 150000]],
            ['kode' => 'XR010', 'nama' => 'Cervical AP/Lateral', 'modalitas' => 'XRAY', 'region' => 'Vertebra', 't' => [200000, 175000, 150000, 130000]],
            ['kode' => 'XR011', 'nama' => 'Pelvis AP', 'modalitas' => 'XRAY', 'region' => 'Pelvis', 't' => [200000, 175000, 150000, 130000]],

            // USG
            ['kode' => 'US001', 'nama' => 'USG Abdomen Atas', 'modalitas' => 'USG', 'region' => 'Abdomen', 't' => [350000, 300000, 250000, 200000], 'template' => "Hepar: ukuran normal, echotextur homogen, tidak tampak SOL.\nGall bladder: dinding normal, tidak tampak batu.\nPancreas: ukuran normal.\nLien: ukuran normal.\nGinjal kanan/kiri: ukuran normal, tidak tampak batu/hidronefrosis."],
            ['kode' => 'US002', 'nama' => 'USG Abdomen Bawah', 'modalitas' => 'USG', 'region' => 'Abdomen', 't' => [350000, 300000, 250000, 200000]],
            ['kode' => 'US003', 'nama' => 'USG Kandungan (Obstetri)', 'modalitas' => 'USG', 'region' => 'Pelvis', 't' => [400000, 350000, 300000, 250000], 'template' => "Janin tunggal hidup intrauterin.\nDJJ: ... bpm, regular.\nUsia kehamilan berdasarkan BPD: ... minggu.\nTaksiran berat janin: ... gram.\nPlasenta: korpus posterior, gradasi ..., tidak menutupi OUI.\nLetak janin: ..."],
            ['kode' => 'US004', 'nama' => 'USG Ginekologi', 'modalitas' => 'USG', 'region' => 'Pelvis', 't' => [350000, 300000, 250000, 200000]],
            ['kode' => 'US005', 'nama' => 'USG Tiroid', 'modalitas' => 'USG', 'region' => 'Leher', 't' => [300000, 250000, 220000, 180000]],
            ['kode' => 'US006', 'nama' => 'USG Mammae', 'modalitas' => 'USG', 'region' => 'Thorax', 't' => [400000, 350000, 300000, 250000]],
            ['kode' => 'US007', 'nama' => 'USG Doppler Vaskular', 'modalitas' => 'USG', 'region' => 'Ekstremitas', 't' => [450000, 400000, 350000, 300000]],

            // CT-Scan (opsional, kalau RS punya)
            ['kode' => 'CT001', 'nama' => 'CT-Scan Kepala Non-Kontras', 'modalitas' => 'CT', 'region' => 'Kepala', 't' => [1200000, 1000000, 850000, 750000]],
            ['kode' => 'CT002', 'nama' => 'CT-Scan Thorax', 'modalitas' => 'CT', 'region' => 'Thorax', 't' => [1500000, 1300000, 1100000, 950000]],
        ];

        foreach ($data as $d) {
            PemeriksaanRadiologi::firstOrCreate(
                ['kode' => $d['kode']],
                [
                    'nama' => $d['nama'],
                    'modalitas' => $d['modalitas'],
                    'region' => $d['region'],
                    'tarif_vip' => $d['t'][0],
                    'tarif_kelas1' => $d['t'][1],
                    'tarif_kelas2' => $d['t'][2],
                    'tarif_kelas3' => $d['t'][3],
                    'template_bacaan' => $d['template'] ?? null,
                ]
            );
        }

        $this->command->info('  ✅ '.count($data).' pemeriksaan radiologi ter-seed');
    }
}
