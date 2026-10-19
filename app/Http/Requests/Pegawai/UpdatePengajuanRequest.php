<?php

namespace App\Http\Requests\Pegawai;

use App\Models\Pengajuan;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengajuanRequest extends FormRequest
{
    use AturanPengajuan;

    public function authorize(): bool
    {
        // Kepemilikan dan syarat status REVISI diperiksa Gate::authorize('update')
        // di controller; di sini cukup dipastikan perannya boleh mengajukan.
        return $this->user()?->can('create', Pengajuan::class) ?? false;
    }

    public function rules(): array
    {
        return array_merge([
            'tanggal_pengajuan' => ['required', 'date'],
            // Ikut divalidasi karena update() menulis ulang kolom-kolom ini;
            // tanpa input yang sepadan, perbaikan pengajuan akan mengosongkannya.
            'bulan_kegiatan' => ['required', 'date'],
            'nama_penerima' => ['required', 'string', 'max:255'],
            'keterangan' => ['required', 'string', 'max:5000'],
            'total_nominal' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'catatan_pengaju' => ['nullable', 'string', 'max:5000'],
        ], $this->aturanTim(), $this->aturanPenerima());
    }

    public function messages(): array
    {
        return $this->pesanPenerima();
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
