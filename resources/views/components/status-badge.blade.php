@props([
    'status' => null,
    'aktif' => false,

    // kompatibilitas pemanggilan lama
    'kode' => null,
    'label' => null,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Normalisasi status
    |--------------------------------------------------------------------------
    |
    | Bisa menerima:
    | - Model StatusVerifikasi / StatusPencairan
    | - String: ACC, SESUAI, SELESAI, dll
    | - API lama: kode + label
    | - null
    |
    */

    $kodeStatus = is_object($status) ? $status->kode_status ?? null : ($status ?: $kode);

    $namaStatus = is_object($status) ? $status->nama_status ?? null : null;

    /*
    |--------------------------------------------------------------------------
    | State Badge
    |--------------------------------------------------------------------------
    */

    if (!$kodeStatus || $kodeStatus === 'MENUNGGU') {
        if ($aktif) {
            $displayLabel = 'Sedang Diproses';

            $classes = 'border-status-pending/30 ' . 'bg-status-pending/10 ' . 'text-status-pending';
        } else {
            $displayLabel = 'Belum Dimulai';

            $classes = 'border-status-neutral/30 ' . 'bg-status-neutral/10 ' . 'text-status-neutral';
        }
    } elseif (in_array($kodeStatus, ['ACC', 'SESUAI'], true)) {
        $displayLabel = $label ?? 'Disetujui';

        $classes = 'border-status-approved/30 ' . 'bg-status-approved/10 ' . 'text-status-approved';
    } elseif ($kodeStatus === 'SELESAI') {
        $displayLabel = $label ?? 'Selesai';

        $classes = 'border-status-done/30 ' . 'bg-status-done/10 ' . 'text-status-done';
    } elseif ($kodeStatus === 'REVISI') {
        $displayLabel = $label ?? 'Revisi';

        $classes = 'border-status-revisi/30 ' . 'bg-status-revisi/10 ' . 'text-status-revisi';
    } elseif (in_array($kodeStatus, ['TOLAK', 'DITOLAK', 'TIDAK_SESUAI'], true)) {
        $displayLabel = $label ?? 'Ditolak';

        $classes = 'border-status-rejected/30 ' . 'bg-status-rejected/10 ' . 'text-status-rejected';
    } else {
        $displayLabel = $label ?? ($namaStatus ?? ucwords(strtolower(str_replace('_', ' ', $kodeStatus))));

        $classes = $aktif
            ? 'border-status-pending/30 bg-status-pending/10 text-status-pending'
            : 'border-status-neutral/30 bg-status-neutral/10 text-status-neutral';
    }
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center rounded-full border px-3 py-1 ' . 'text-xs font-semibold ' . $classes,
    ]) }}>
    {{ $displayLabel }}
</span>
