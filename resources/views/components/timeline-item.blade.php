@props(['label', 'status' => null, 'aktif' => false, 'waktu' => null, 'catatan' => null])

<div class="relative flex gap-4">

    {{-- Garis timeline --}}
    <div class="flex flex-col items-center">

        {{-- Bulatan --}}
        <div @class([
            'relative z-10 flex h-10 w-10 items-center justify-center rounded-full border-2',
            'border-amber-400 bg-amber-50' => $aktif && !$status,
            'border-gray-300 bg-gray-50' => !$aktif && !$status,
            'border-green-500 bg-green-50' =>
                $status &&
                in_array($status->kode_status ?? null, ['ACC', 'SESUAI', 'SELESAI']),
            'border-orange-500 bg-orange-50' =>
                ($status->kode_status ?? null) === 'REVISI',
            'border-red-500 bg-red-50' =>
                $status &&
                in_array($status->kode_status ?? null, [
                    'TOLAK',
                    'DITOLAK',
                    'TIDAK_SESUAI',
                ]),
        ])>

            @if ($status && in_array($status->kode_status ?? null, ['ACC', 'SESUAI', 'SELESAI']))
                <span class="font-bold text-green-600">
                    ✓
                </span>
            @elseif ($status && in_array($status->kode_status ?? null, ['TOLAK', 'DITOLAK', 'TIDAK_SESUAI']))
                <span class="font-bold text-red-600">
                    ×
                </span>
            @elseif (($status->kode_status ?? null) === 'REVISI')
                <span class="font-bold text-orange-600">
                    !
                </span>
            @elseif ($aktif)
                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
            @else
                <span class="h-3 w-3 rounded-full bg-gray-300"></span>
            @endif

        </div>

        {{-- Garis ke bawah --}}
        <div class="h-full min-h-[70px] w-px bg-gray-200"></div>

    </div>


    {{-- Isi timeline --}}
    <div class="flex-1 pb-8">

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

            <div class="flex flex-col gap-3
                       sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <h4 class="font-semibold text-gray-900">
                        {{ $label }}
                    </h4>

                    @if ($waktu)
                        <p class="mt-1 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($waktu)->format('d M Y H:i') }}
                        </p>
                    @endif

                </div>


                <x-status-badge :status="$status" :aktif="$aktif" />

            </div>


            @if ($catatan)
                <div class="mt-4 rounded-lg bg-gray-50 p-3">

                    <p
                        class="text-xs font-semibold uppercase
                               tracking-wide text-gray-400">
                        Catatan
                    </p>

                    <p class="mt-1 text-sm text-gray-700">
                        {{ $catatan }}
                    </p>

                </div>
            @endif

        </div>

    </div>

</div>
