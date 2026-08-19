<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('pegawai') ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal_pengajuan' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'keterangan' => ['required', 'string', 'max:5000'],
            'total_nominal' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'catatan_pengaju' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
