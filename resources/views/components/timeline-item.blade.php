@props([
    'judul',
    'kodeStatus',
    'namaStatus',
    'waktu' => null,
    'catatan' => null,
    'aktor' => null,
])

<div class="relative border-l-2 border-ui-border pl-6 pb-6 last:pb-0">
    <span class="absolute -left-2 top-1.5 h-3.5 w-3.5 rounded-full bg-status-neutral ring-4 ring-ui-card"></span>

    <div class="rounded-xl border border-ui-border bg-ui-card p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold text-ui-text">{{ $judul }}</h3>
                @if($aktor)
                    <p class="mt-1 text-sm text-ui-muted">Oleh: {{ $aktor }}</p>
                @endif
            </div>
            <x-status-badge :kode="$kodeStatus" :label="$namaStatus" />
        </div>

        @if($catatan)
            <p class="mt-3 whitespace-pre-line text-sm text-ui-text">{{ $catatan }}</p>
        @endif

        @if($waktu)
            <p class="mt-3 text-xs text-ui-muted">
                {{ \Illuminate\Support\Carbon::parse($waktu)->format('d M Y H:i') }}
            </p>
        @endif
    </div>
</div>
