@props(['kode', 'label' => null])

@php
    $approved = ['ACC', 'SESUAI'];
    $revision = ['REVISI'];
    $rejected = ['DITOLAK', 'TOLAK', 'TIDAK_SESUAI'];
    $done = ['SELESAI'];
    $neutral = ['DRAFT'];

    $class = match (true) {
        in_array($kode, $approved, true) => 'bg-status-approved/10 text-status-approved border-status-approved/20',
        in_array($kode, $revision, true) => 'bg-status-revisi/10 text-status-revisi border-status-revisi/20',
        in_array($kode, $rejected, true) => 'bg-status-rejected/10 text-status-rejected border-status-rejected/20',
        in_array($kode, $done, true) => 'bg-status-done/10 text-status-done border-status-done/20',
        in_array($kode, $neutral, true) => 'bg-status-neutral/10 text-status-neutral border-status-neutral/20',
        default => 'bg-status-pending/10 text-status-pending border-status-pending/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {$class}"]) }}>
    {{ $label ?? str($kode)->replace('_', ' ')->title() }}
</span>
