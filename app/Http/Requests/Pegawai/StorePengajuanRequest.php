<?php

namespace App\Http\Requests\Pegawai;

use App\Models\Pengajuan;
use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    use AturanPengajuan;

    public function authorize(): bool
    {
        // Daftar peran pengaju dipusatkan di PengajuanPolicy agar rute,
        // policy, dan form request tidak bisa berbeda pendapat.
        return $this->user()?->can('create', Pengajuan::class) ?? false;
    }

    public function rules(): array
    {
        return array_merge([
            'tanggal_pengajuan' => ['required', 'date'],
            'bulan_kegiatan'    => ['required', 'date'],
            'nama_penerima'     => ['required', 'string', 'max:255'],
            'keterangan'        => ['required', 'string'],
            'total_nominal'     => ['required', 'numeric', 'min:0'],
            'catatan_pengaju'   => ['nullable', 'string'],
        ], $this->aturanTim(), $this->aturanPenerima());
    }

    public function messages(): array
    {
        return array_merge([
            'tanggal_pengajuan.required' => 'Tanggal pengajuan wajib diisi.',
            'perihal.required' => 'Perihal wajib diisi.',
            'keterangan.required' => 'Ringkasan/keterangan SPJ wajib diisi.',
            'total_nominal.required' => 'Total nominal wajib diisi.',
            'total_nominal.numeric' => 'Total nominal harus berupa angka.',
        ], $this->pesanPenerima());
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $this->periksaTotalPenerima($validator);
    }

    protected function prepareForValidation(): void
    {
        $this->normalkanRupiah();
    }
}
