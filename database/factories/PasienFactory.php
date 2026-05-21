<?php

namespace Database\Factories;

use App\Enums\JenisKelamin;
use App\Models\Pasien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pasien>
 */
class PasienFactory extends Factory
{
    protected $model = Pasien::class;

    public function definition(): array
    {
        $jk = fake()->randomElement([JenisKelamin::LakiLaki, JenisKelamin::Perempuan]);
        $namaDepan = $jk === JenisKelamin::LakiLaki
            ? fake()->randomElement([
                'Budi',
                'Ahmad',
                'Surya',
                'Rian',
                'Dimas',
                'Andre',
                'Heru',
                'Fauzan',
                'Iqbal',
                'Reza',
                'Bayu',
                'Hendra',
                'Anton',
                'Yusuf',
                'Doni',
                'Joko',
                'Rendy',
                'Bambang',
                'Eko',
                'Agus',
            ])
            : fake()->randomElement([
                'Siti',
                'Dewi',
                'Rina',
                'Sari',
                'Maya',
                'Lina',
                'Putri',
                'Indah',
                'Wulan',
                'Ayu',
                'Ratna',
                'Mega',
                'Fitri',
                'Nurul',
                'Hanna',
                'Sri',
                'Yuli',
                'Tika',
                'Ani',
                'Lia',
            ]);

        $namaBelakang = fake()->randomElement([
            'Pratama',
            'Saputra',
            'Hidayat',
            'Nugroho',
            'Wijaya',
            // Marga Batak (umum di Sumut)
            'Lubis',
            'Sinaga',
            'Tarigan',
            'Siahaan',
            'Sembiring',
            'Hutapea',
            'Manurung',
            'Hasibuan',
            'Nasution',
            'Harahap',
            'Simanjuntak',
            'Sitorus',
            'Panggabean',
            'Ginting',
            'Purba',
        ]);

        $tglLahir = fake()->dateTimeBetween('-85 years', '-1 month');

        // Generate NIK realistis (16 digit)
        // Format: PPKKKK DDMMYY XXXX
        // PP=provinsi, KK=kab/kota, KKKK=kec
        $kabKota = fake()->randomElement(['1271', '1275', '1208', '1212']); // Medan, Binjai, Langkat, Deli Serdang
        $tglNik = $jk === JenisKelamin::Perempuan
            ? str_pad((int) $tglLahir->format('d') + 40, 2, '0', STR_PAD_LEFT) // wanita: tgl + 40
            : $tglLahir->format('d');
        $nik = $kabKota
            . fake()->numberBetween(10, 99)  // kecamatan 2 digit
            . $tglNik
            . $tglLahir->format('m')
            . $tglLahir->format('y')
            . fake()->numerify('####');

        $kota = fake()->randomElement([
            'Medan',
            'Binjai',
            'Tebing Tinggi',
            'Pematangsiantar',
            'Deli Serdang',
            'Langkat',
            'Lubuk Pakam',
            'Stabat',
        ]);

        $jalan = fake()->randomElement([
            'Jl. Merdeka',
            'Jl. Sudirman',
            'Jl. Diponegoro',
            'Jl. Imam Bonjol',
            'Jl. Gatot Subroto',
            'Jl. Ahmad Yani',
            'Jl. Sisingamangaraja',
            'Jl. Letda Sujono',
            'Jl. Setia Budi',
            'Jl. Jamin Ginting',
            'Jl. AR Hakim',
            'Jl. HM Yamin',
            'Jl. Pancing',
            'Jl. Pelita',
        ]);

        return [
            'nik' => $nik,
            'nama' => "{$namaDepan} {$namaBelakang}",
            'tempat_lahir' => $kota,
            'tgl_lahir' => $tglLahir->format('Y-m-d'),
            'jenis_kelamin' => $jk,
            'status_pernikahan' => fake()->randomElement(['BELUM_KAWIN', 'KAWIN', 'KAWIN', 'CERAI_HIDUP', 'CERAI_MATI']),
            'agama' => fake()->randomElement(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'pendidikan' => fake()->randomElement(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2']),
            'pekerjaan' => fake()->randomElement([
                'Pelajar/Mahasiswa',
                'Wiraswasta',
                'Karyawan Swasta',
                'PNS',
                'TNI/POLRI',
                'Petani',
                'Buruh',
                'Ibu Rumah Tangga',
                'Pensiunan',
                'Guru',
                'Dokter',
                'Perawat',
                'Tidak Bekerja',
            ]),
            'alamat' => "{$jalan} No. " . fake()->numberBetween(1, 250),
            'rt' => str_pad((string) fake()->numberBetween(1, 20), 2, '0', STR_PAD_LEFT),
            'rw' => str_pad((string) fake()->numberBetween(1, 15), 2, '0', STR_PAD_LEFT),
            'kelurahan' => fake()->randomElement([
                'Glugur Darat',
                'Pulo Brayan',
                'Tegal Sari',
                'Helvetia',
                'Sunggal',
                'Medan Petisah',
                'Polonia',
                'Medan Maimun',
                'Padang Bulan',
            ]),
            'kecamatan' => fake()->randomElement([
                'Medan Timur',
                'Medan Barat',
                'Medan Kota',
                'Medan Helvetia',
                'Medan Sunggal',
                'Medan Petisah',
                'Medan Polonia',
                'Medan Maimun',
                'Medan Baru',
                'Medan Tembung',
            ]),
            'kabupaten' => $kota,
            'provinsi' => 'Sumatera Utara',
            'kode_pos' => fake()->numerify('2####'),
            'telp' => '08' . fake()->randomElement(['12', '13', '21', '52', '53', '77', '81', '82'])
                . fake()->numerify('########'),
            'email' => fake()->boolean(40) ? fake()->safeEmail() : null,
            'gol_darah' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', null, null]),
            'nama_ayah' => fake()->boolean(80) ? fake()->randomElement(['Budi', 'Hasan', 'Surya', 'Ahmad', 'Iwan']) . ' ' . $namaBelakang : null,
            'nama_ibu' => fake()->boolean(85) ? fake()->randomElement(['Siti', 'Dewi', 'Ratna', 'Mariam', 'Aminah']) . ' ' . fake()->randomElement(['Lubis', 'Nasution', 'Hasibuan', 'Wati']) : null,
            'kontak_darurat_nama' => fake()->boolean(70) ? fake()->name() : null,
            'kontak_darurat_hubungan' => fake()->randomElement(['Suami', 'Istri', 'Anak', 'Orang Tua', 'Saudara']),
            'kontak_darurat_telp' => fake()->boolean(70) ? '08' . fake()->numerify('##########') : null,
        ];
    }

    /**
     * Pasien anak (<18 tahun).
     */
    public function anak(): static
    {
        return $this->state(fn() => [
            'tgl_lahir' => fake()->dateTimeBetween('-17 years', '-1 month')->format('Y-m-d'),
            'status_pernikahan' => 'BELUM_KAWIN',
            'pendidikan' => fake()->randomElement(['Belum Sekolah', 'SD', 'SMP']),
            'pekerjaan' => 'Pelajar/Mahasiswa',
        ]);
    }

    /**
     * Pasien lansia (>60 tahun).
     */
    public function lansia(): static
    {
        return $this->state(fn() => [
            'tgl_lahir' => fake()->dateTimeBetween('-90 years', '-60 years')->format('Y-m-d'),
            'pekerjaan' => fake()->randomElement(['Pensiunan', 'Tidak Bekerja', 'Wiraswasta']),
        ]);
    }
}
