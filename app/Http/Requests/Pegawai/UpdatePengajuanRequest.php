<?php

namespace App\Http\Requests\Pegawai;

use App\Models\Pengajuan;
use App\Support\Rupiah;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Kepemilikan dan syarat status REVISI diperiksa Gate::authorize('update')
        // di controller; di sini cukup dipastikan perannya boleh mengajukan.
        return $this->user()?->can('create', Pengajuan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal_pengajuan' => ['required', 'date'],
            // Ikut divalidasi karena update() menulis ulang kolom-kolom ini;
            // tanpa input yang sepadan, perbaikan pengajuan akan mengosongkannya.
            'bulan_kegiatan' => ['required', 'date'],
            'nama_penerima' => ['required', 'string', 'max:255'],
            'keterangan' => ['required', 'string', 'max:5000'],
            'total_nominal' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'catatan_pengaju' => ['nullable', 'string', 'max:5000'],
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
