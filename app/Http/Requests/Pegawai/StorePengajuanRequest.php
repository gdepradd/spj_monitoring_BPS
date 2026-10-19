<?php

namespace App\Http\Requests\Pegawai;

use App\Models\Pengajuan;
use App\Support\Rupiah;
use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Daftar peran pengaju dipusatkan di PengajuanPolicy agar rute,
        // policy, dan form request tidak bisa berbeda pendapat.
        return $this->user()?->can('create', Pengajuan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal_pengajuan'              => ['required', 'date'],
            'bulan_kegiatan'                 => ['required', 'date'],
            'nama_penerima'                  => ['required', 'string', 'max:255'],
            'keterangan'                     => ['required', 'string'],
            'total_nominal'                  => ['required', 'numeric', 'min:0'],
            'catatan_pengaju'                => ['nullable', 'string'],
            // Daftar penerima bersifat opsional; bila diisi, tiap baris wajib ada nama.
            'daftar_penerima'                => ['nullable', 'array'],
            'daftar_penerima.*.nama'         => ['required_with:daftar_penerima', 'string', 'max:255'],
            'daftar_penerima.*.nominal'      => ['nullable', 'numeric', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'tanggal_pengajuan.required' => 'Tanggal pengajuan wajib diisi.',
            'perihal.required' => 'Perihal wajib diisi.',
            'keterangan.required' => 'Ringkasan/keterangan SPJ wajib diisi.',
            'total_nominal.required' => 'Total nominal wajib diisi.',
            'total_nominal.numeric' => 'Total nominal harus berupa angka.',
        ];
    }

    /**
     * Angka rupiah berpemisah ribuan dinormalkan sebelum divalidasi.
     * Lihat App\Support\Rupiah::keAngka() untuk alasannya.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('total_nominal')) {
            $this->merge([
                'total_nominal' => Rupiah::keAngka($this->input('total_nominal')),
            ]);
        }
    }
}
