<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('pegawai') ?? false;
    }

    public function rules(): array
{
    return [
        'tanggal_pengajuan' => ['required', 'date'],
        'bulan_kegiatan'    => ['required', 'date'],
        'tim_pengaju'       => ['required', 'string', 'max:255'],
        'nama_penerima'     => ['required', 'string', 'max:255'],
        'keterangan'        => ['required', 'string'],
        'total_nominal'     => ['required', 'numeric', 'min:0'],
        'catatan_pengaju'   => ['nullable', 'string'],
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
}
