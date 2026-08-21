@props([
    'label' => null,
    'status' => null,
    'aktif' => false,
    'waktu' => null,
    'catatan' => null,
    'aktor' => null,

    // kompatibilitas komponen lama
    'judul' => null,
    'kodeStatus' => null,
    'namaStatus' => null,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Normalisasi API lama + baru
    |--------------------------------------------------------------------------
    */

    $resolvedLabel = $label ?? ($judul ?? 'Tahap');

    $resolvedStatus = $status ?? $kodeStatus;

    $kode = is_object($resolvedStatus) ? $resolvedStatus->kode_status ?? null : $resolvedStatus;

    /*
    |--------------------------------------------------------------------------
    | Tentukan warna indicator
    |--------------------------------------------------------------------------
    */

    if (in_array($kode, ['ACC', 'SESUAI'], true)) {
        $circleClass = 'border-status-approved ' . 'bg-status-approved/10';

        $icon = '✓';
        $iconClass = 'text-status-approved';
    } elseif ($kode === 'SELESAI') {
        $circleClass = 'border-status-done ' . 'bg-status-done/10';

        $icon = '✓';
        $iconClass = 'text-status-done';
    } elseif ($kode === 'REVISI') {
        $circleClass = 'border-status-revisi ' . 'bg-status-revisi/10';

        $icon = '!';
        $iconClass = 'text-status-revisi';
    } elseif (in_array($kode, ['TOLAK', 'DITOLAK', 'TIDAK_SESUAI'], true)) {
        $circleClass = 'border-status-rejected ' . 'bg-status-rejected/10';

        $icon = '×';
        $iconClass = 'text-status-rejected';
    } elseif ($aktif) {
        $circleClass = 'border-status-pending ' . 'bg-status-pending/10';

        $icon = '•';
        $iconClass = 'text-status-pending';
    } else {
        $circleClass = 'border-status-neutral/40 ' . 'bg-status-neutral/10';

        $icon = '•';
        $iconClass = 'text-status-neutral';
    }
@endphp


<div class="relative flex gap-4">

    {{-- Indicator --}}
    <div class="flex flex-col items-center">

        <div
            class="
                relative z-10 flex h-10 w-10
                items-center justify-center
                rounded-full border-2
                {{ $circleClass }}
            ">
            <span class="text-lg font-bold {{ $iconClass }}">
                {{ $icon }}
            </span>
        </div>


        {{-- Garis timeline --}}
        <div class="
                h-full min-h-[70px]
                w-px bg-ui-border
            "></div>

    </div>


    {{-- Content --}}
    <div class="flex-1 pb-8">

        <div
            class="
                rounded-xl border border-ui-border
                bg-ui-card p-4 shadow-sm
            ">

            <div
                class="
                    flex flex-col gap-3
                    sm:flex-row
                    sm:items-start
                    sm:justify-between
                ">

                <div>

                    <h4 class="font-semibold text-ui-text">
                        {{ $resolvedLabel }}
                    </h4>


                    @if ($aktor)
                        <p class="mt-1 text-xs text-ui-muted">
                            {{ $aktor }}
                        </p>
                    @endif


                    @if ($waktu)
                        <p class="mt-1 text-xs text-ui-muted">
                            {{ \Carbon\Carbon::parse($waktu)->format('d M Y H:i') }}
                        </p>
                    @endif

                </div>


                <x-status-badge :status="$resolvedStatus" :aktif="$aktif" :label="$namaStatus" />

            </div>


            @if ($catatan)
                <div
                    class="
                        mt-4 rounded-lg
                        bg-ui-page p-3
                    ">

                    <p
                        class="
                            text-xs font-semibold uppercase
                            tracking-wide text-ui-muted
                        ">
                        Catatan
                    </p>

                    <p class="mt-1 text-sm text-ui-text">
                        {{ $catatan }}
                    </p>

                </div>
            @endif

        </div>

    </div>

</div>
