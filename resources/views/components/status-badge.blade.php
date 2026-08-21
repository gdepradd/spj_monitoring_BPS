@props([
    'status' => null,
    'aktif' => false,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Ambil kode status
    |--------------------------------------------------------------------------
    |
    | $status bisa:
    | - null
    | - object model StatusVerifikasi / StatusPencairan
    | - string kode status
    |
    */

    if (is_object($status)) {
        $kode = $status->kode_status ?? null;
    } else {
        $kode = $status;
    }

    /*
    |--------------------------------------------------------------------------
    | Tentukan state timeline
    |--------------------------------------------------------------------------
    */

    if (!$kode) {
        if ($aktif) {
            $label = 'Sedang Diproses';
            $class = 'bg-amber-100 text-amber-700 border-amber-200';
        } else {
            $label = 'Belum Dimulai';
            $class = 'bg-gray-100 text-gray-600 border-gray-200';
        }
    } elseif (in_array($kode, ['ACC', 'SESUAI'])) {
        $label = 'Disetujui';
        $class = 'bg-green-100 text-green-700 border-green-200';
    } elseif ($kode === 'SELESAI') {
        $label = 'Selesai';
        $class = 'bg-green-100 text-green-700 border-green-200';
    } elseif ($kode === 'REVISI') {
        $label = 'Revisi';
        $class = 'bg-orange-100 text-orange-700 border-orange-200';
    } elseif (in_array($kode, ['TOLAK', 'DITOLAK', 'TIDAK_SESUAI'])) {
        $label = 'Ditolak';
        $class = 'bg-red-100 text-red-700 border-red-200';
    } elseif ($kode === 'MENUNGGU') {
        if ($aktif) {
            $label = 'Sedang Diproses';
            $class = 'bg-amber-100 text-amber-700 border-amber-200';
        } else {
            $label = 'Belum Dimulai';
            $class = 'bg-gray-100 text-gray-600 border-gray-200';
        }
    } else {
        $label = is_object($status) ? $status->nama_status ?? $kode : $kode;

        $class = 'bg-gray-100 text-gray-600 border-gray-200';
    }
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ' . $class,
    ]) }}>
    {{ $label }}
</span>
