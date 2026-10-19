<?php

namespace App\Http\Requests\Pegawai;

use App\Support\Rupiah;
use Illuminate\Validation\Rule;

/**
 * Aturan yang dipakai bersama oleh form pengajuan baru dan form revisi.
 *
 * Keduanya mengisi kolom yang sama, jadi aturannya dipusatkan di sini supaya
 * tidak bisa berbeda pendapat — mis. daftar penerima yang lolos saat membuat
 * pengajuan tapi tertolak saat merevisinya.
 */
trait AturanPengajuan
{
    /**
     * Tim pengaju. Hanya tim yang benar-benar diikuti akun ini yang sah,
     * sehingga nilai dari form tidak bisa dipakai mengaku-aku tim orang lain.
     *
     * @return array<string, mixed>
     */
    protected function aturanTim(): array
    {
        $idTim = $this->user()?->tim->pluck('id_tim')->all() ?? [];

        return [
            'id_tim' => ['nullable', Rule::in($idTim)],
        ];
    }

    /**
     * Daftar penerima bersifat opsional — SPJ dengan satu penerima cukup
     * mengandalkan kolom nama_penerima.
     *
     * Bila diisi, totalnya tidak boleh melebihi total nominal pengajuan: uang
     * yang dibagikan tidak mungkin lebih besar dari yang diajukan. Total yang
     * lebih kecil dibiarkan lolos — ada SPJ yang memang hanya merinci sebagian
     * penerimanya — dan selisihnya ditampilkan di form sebagai peringatan.
     *
     * @return array<string, mixed>
     */
    protected function aturanPenerima(): array
    {
        return [
            'penerima' => ['nullable', 'array', 'max:1000'],
            'penerima.*.nama_penerima' => ['required', 'string', 'max:150'],
            'penerima.*.nominal' => ['required', 'numeric', 'min:1', 'max:9999999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function pesanPenerima(): array
    {
        return [
            'id_tim.in' => 'Tim yang dipilih bukan tim yang Anda ikuti.',
            'penerima.max' => 'Daftar penerima maksimal 1.000 baris dalam satu pengajuan.',
            'penerima.*.nama_penerima.required' => 'Nama penerima pada daftar wajib diisi.',
            'penerima.*.nama_penerima.max' => 'Nama penerima maksimal 150 karakter.',
            'penerima.*.nominal.required' => 'Nominal tiap penerima pada daftar wajib diisi.',
            'penerima.*.nominal.numeric' => 'Nominal penerima harus berupa angka.',
            'penerima.*.nominal.min' => 'Nominal tiap penerima minimal 1.',
        ];
    }

    /**
     * Total daftar penerima tidak boleh melampaui total nominal pengajuan.
     *
     * Diperiksa setelah aturan dasar lolos (withValidator), bukan sebagai rule
     * tersendiri, supaya pesan "nominal harus angka" tidak tertimpa pesan
     * perbandingan total yang menyesatkan.
     */
    protected function periksaTotalPenerima(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $baris = $this->input('penerima', []);

            if (! is_array($baris) || $baris === []) {
                return;
            }

            $totalPenerima = collect($baris)
                ->sum(fn ($item) => (float) (is_array($item) ? ($item['nominal'] ?? 0) : 0));

            $totalPengajuan = (float) $this->input('total_nominal');

            if ($totalPenerima > $totalPengajuan) {
                $validator->errors()->add('penerima', sprintf(
                    'Total daftar penerima (Rp %s) melebihi total nominal pengajuan (Rp %s).',
                    number_format($totalPenerima, 0, ',', '.'),
                    number_format($totalPengajuan, 0, ',', '.')
                ));
            }
        });
    }

    /**
     * Angka rupiah berpemisah ribuan dinormalkan sebelum divalidasi, baik total
     * nominal maupun nominal tiap penerima.
     *
     * Tanpa ini, '200.000' lolos aturan 'numeric' sebagai 200 dan tersimpan
     * sebagai dua ratus rupiah tanpa peringatan. Lihat App\Support\Rupiah::keAngka().
     */
    protected function normalkanRupiah(): void
    {
        if ($this->has('total_nominal')) {
            $this->merge([
                'total_nominal' => Rupiah::keAngka($this->input('total_nominal')),
            ]);
        }

        $penerima = $this->input('penerima');

        if (! is_array($penerima)) {
            return;
        }

        $bersih = [];

        foreach ($penerima as $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $nama = trim((string) ($baris['nama_penerima'] ?? ''));
            $nominal = (string) ($baris['nominal'] ?? '');

            // Baris yang sama sekali kosong adalah baris repeater yang tidak
            // terpakai, bukan kesalahan pengisian, jadi dibuang tanpa pesan.
            // Baris yang terisi separuh tetap lolos ke validasi agar pengaju
            // diberi tahu bagian mana yang belum lengkap.
            if ($nama === '' && $nominal === '') {
                continue;
            }

            $baris['nama_penerima'] = $nama;
            $baris['nominal'] = Rupiah::keAngka($nominal);

            $bersih[] = $baris;
        }

        $this->merge(['penerima' => $bersih]);
    }
}
