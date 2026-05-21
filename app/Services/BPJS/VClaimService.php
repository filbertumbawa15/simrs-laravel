<?php

namespace App\Services\BPJS;

use App\Models\AsuransiPasien;
use App\Models\Kunjungan;

/**
 * V-Claim service — endpoint paling sering dipakai RS:
 * - cekPeserta: validasi kepesertaan BPJS (sebelum buat SEP)
 * - buatSep: generate Surat Eligibilitas Peserta
 * - cariRujukan: cek rujukan dari FKTP
 * - kirimKlaim: submit klaim INA-CBGs (di RS biasanya pakai apl V-Claim langsung, ini opsional)
 */
class VClaimService
{
    public function __construct(protected BPJSClient $client) {}

    public static function make(): self
    {
        return new self(BPJSClient::vclaim());
    }

    /**
     * Cek peserta berdasarkan No. Kartu BPJS.
     * @return array ['noKartu', 'nik', 'nama', 'jenisKelamin', 'tglLahir', 'hakKelas', ...]
     */
    public function cekPesertaByNoKartu(string $noKartu, ?string $tglPelayanan = null): array
    {
        $tgl = $tglPelayanan ?? now()->format('Y-m-d');
        $response = $this->client->get("Peserta/nokartu/{$noKartu}/tglSEP/{$tgl}");
        return $response['response']['peserta'] ?? [];
    }

    public function cekPesertaByNik(string $nik, ?string $tglPelayanan = null): array
    {
        $tgl = $tglPelayanan ?? now()->format('Y-m-d');
        $response = $this->client->get("Peserta/nik/{$nik}/tglSEP/{$tgl}");
        return $response['response']['peserta'] ?? [];
    }

    /**
     * Cari rujukan dari FKTP berdasarkan no. rujukan / no. kartu.
     */
    public function cariRujukan(string $noRujukan, string $source = 'Rujukan'): array
    {
        // $source: 'Rujukan' (FKTP), 'RujukanRS' (antar RS)
        $endpoint = $source === 'RujukanRS' ? 'RujukanRS' : 'Rujukan';
        $response = $this->client->get("{$endpoint}/{$noRujukan}");
        return $response['response'] ?? [];
    }

    /**
     * Buat SEP baru. Payload mengikuti spesifikasi BPJS V-Claim.
     */
    public function buatSep(Kunjungan $kunjungan, array $extra = []): array
    {
        $asuransiPasien = $kunjungan->asuransiPasien;
        if (! $asuransiPasien) {
            throw new BPJSException('Pasien tidak terdaftar BPJS', 400);
        }

        $payload = [
            'request' => [
                't_sep' => array_merge([
                    'noKartu' => $asuransiPasien->no_polis,
                    'tglSep' => $kunjungan->tgl_masuk->format('Y-m-d'),
                    'ppkPelayanan' => config('app.rs.kode'),
                    'jnsPelayanan' => $kunjungan->tipe->value === 'RJ' ? '2' : '1', // 1=RI, 2=RJ
                    'klsRawat' => [
                        'klsRawatHak' => $asuransiPasien->kelas_hak ?? '3',
                        'klsRawatNaik' => '',
                        'pembiayaan' => '',
                        'penanggungJawab' => '',
                    ],
                    'noMR' => $kunjungan->pasien->no_rm,
                    'rujukan' => [
                        'asalRujukan' => $extra['asalRujukan'] ?? '1', // 1=FKTP
                        'tglRujukan' => $extra['tglRujukan'] ?? now()->format('Y-m-d'),
                        'noRujukan' => $kunjungan->no_rujukan ?? '',
                        'ppkRujukan' => $extra['ppkRujukan'] ?? '',
                    ],
                    'catatan' => $extra['catatan'] ?? '',
                    'diagAwal' => $extra['diagAwal'] ?? '',
                    'poli' => [
                        'tujuan' => $extra['poliTujuan'] ?? '',
                        'eksekutif' => $extra['eksekutif'] ?? '0',
                    ],
                    'cob' => ['cob' => '0'],
                    'katarak' => ['katarak' => '0'],
                    'jaminan' => [
                        'lakaLantas' => $extra['lakaLantas'] ?? '0',
                        'penjamin' => ['tglKejadian' => '', 'keterangan' => '', 'suplesi' => ['suplesi' => '0']],
                    ],
                    'tujuanKunj' => $extra['tujuanKunj'] ?? '0',
                    'flagProcedure' => '',
                    'kdPenunjang' => '',
                    'assesmentPel' => '',
                    'skdp' => ['noSurat' => '', 'kodeDPJP' => ''],
                    'dpjpLayan' => $extra['dpjpLayan'] ?? '',
                    'noTelp' => $kunjungan->pasien->telp ?? '',
                    'user' => auth()->user()?->name ?? 'SYSTEM',
                ], $extra),
            ],
        ];

        $response = $this->client->post('SEP/2.0/insert', $payload);
        $sep = $response['response']['sep'] ?? [];

        // Update kunjungan dengan no_sep yang baru
        if (isset($sep['noSep'])) {
            $kunjungan->update(['no_sep' => $sep['noSep']]);
        }

        return $sep;
    }

    /**
     * Update SEP existing.
     */
    public function updateSep(string $noSep, array $payload): array
    {
        $response = $this->client->put('SEP/2.0/update', [
            'request' => array_merge(['t_sep' => ['noSep' => $noSep]], $payload),
        ]);
        return $response['response'] ?? [];
    }

    /**
     * Hapus SEP (sebelum klaim diajukan).
     */
    public function hapusSep(string $noSep, string $alasan = ''): array
    {
        return $this->client->delete('SEP/2.0/delete', [
            'request' => [
                't_sep' => [
                    'noSep' => $noSep,
                    'user' => auth()->user()?->name ?? 'SYSTEM',
                ],
            ],
        ]);
    }

    /**
     * Get detail SEP by no.
     */
    public function detailSep(string $noSep): array
    {
        $response = $this->client->get("SEP/{$noSep}");
        return $response['response'] ?? [];
    }

    /**
     * Helper: cek + cache info kelas hak peserta sebelum admisi RI.
     */
    public function getKelasHak(AsuransiPasien $asuransi): ?string
    {
        try {
            $peserta = $this->cekPesertaByNoKartu($asuransi->no_polis);
            return $peserta['hakKelas']['keterangan'] ?? null;
        } catch (BPJSException $e) {
            return null;
        }
    }
}
