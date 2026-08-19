<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-purple-600">
                    POV 3 — Pencairan
                </p>

                <h2 class="text-2xl font-bold text-gray-900">
                    Dashboard PPK
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Pengajuan yang telah melalui Verifikator 1, 2, dan 3.
                </p>
            </div>

            <a href="{{ route('ppk.pengajuan.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg
                       bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white
                       shadow-sm transition hover:bg-purple-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z" />
                </svg>

                Daftar Pengajuan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- ====================================================== --}}
            {{-- RINGKASAN --}}
            {{-- ====================================================== --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                {{-- Menunggu --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Menunggu Proses PPK
                            </p>

                            <p class="mt-2 text-3xl font-bold text-purple-600">
                                {{ $totalMenunggu }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center
                                    rounded-xl bg-purple-100 text-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        Pengajuan yang telah lolos Verifikator 3.
                    </p>
                </div>


                {{-- Total nominal --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Total Nominal
                            </p>

                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                Rp {{ number_format($pengajuan->sum('total_nominal'), 0, ',', '.') }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center
                                    rounded-xl bg-purple-100 text-purple-600">
                            <span class="font-bold">
                                Rp
                            </span>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        Total nilai pengajuan yang menunggu diproses.
                    </p>
                </div>


                {{-- Verifikasi lengkap --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

                    @php
                        $verifikasiLengkap = $pengajuan
                            ->filter(function ($item) {
                                return $item->verifikasi
                                    ->whereIn('tahap', [1, 2, 3])
                                    ->pluck('tahap')
                                    ->unique()
                                    ->count() === 3;
                            })
                            ->count();
                    @endphp

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Verifikasi Lengkap
                            </p>

                            <p class="mt-2 text-3xl font-bold text-green-600">
                                {{ $verifikasiLengkap }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center
                                    rounded-xl bg-green-100 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        Pengajuan dengan riwayat Verifikator 1–3 lengkap.
                    </p>
                </div>

            </div>


            {{-- ====================================================== --}}
            {{-- DATA HASIL VERIFIKASI --}}
            {{-- ====================================================== --}}
            <div
                class="mt-6 overflow-hidden rounded-xl border border-gray-200
                        bg-white shadow-sm">

                {{-- Header tabel --}}
                <div
                    class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5
                            sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Data Hasil Verifikasi
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Hasil pemeriksaan Verifikator 1, 2, dan 3 sebelum
                            pengajuan diproses oleh PPK.
                        </p>
                    </div>

                    <span
                        class="inline-flex w-fit rounded-full bg-purple-100
                                 px-3 py-1 text-xs font-semibold text-purple-700">
                        {{ $pengajuan->count() }} Pengajuan
                    </span>
                </div>


                @if ($pengajuan->count() > 0)

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        No. Pengajuan
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        Pengaju
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        Nominal
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        Verifikator 1
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        Verifikator 2
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        Verifikator 3
                                    </th>

                                    <th
                                        class="px-5 py-3 text-center text-xs font-semibold
                                               uppercase tracking-wide text-gray-500">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>


                            <tbody class="divide-y divide-gray-100 bg-white">

                                @foreach ($pengajuan as $item)
                                    @php
                                        $v1 = $item->verifikasi->firstWhere('tahap', 1);
                                        $v2 = $item->verifikasi->firstWhere('tahap', 2);
                                        $v3 = $item->verifikasi->firstWhere('tahap', 3);
                                    @endphp

                                    <tr class="transition hover:bg-gray-50">

                                        {{-- Nomor pengajuan --}}
                                        <td class="whitespace-nowrap px-5 py-4 align-top">

                                            <p class="font-semibold text-gray-900">
                                                {{ $item->no_pengajuan }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                            </p>

                                            <span
                                                class="mt-2 inline-flex rounded-full
                                                       bg-purple-50 px-2.5 py-1
                                                       text-xs font-semibold text-purple-700">
                                                {{ $item->status?->nama_status ?? 'Proses PPK' }}
                                            </span>
                                        </td>


                                        {{-- Pengaju --}}
                                        <td class="px-5 py-4 align-top">

                                            <p class="font-medium text-gray-900">
                                                {{ $item->pemohon?->nama_lengkap ?? '-' }}
                                            </p>

                                            @if ($item->perihal)
                                                <p class="mt-1 max-w-[180px] truncate text-xs text-gray-500"
                                                    title="{{ $item->perihal }}">
                                                    {{ $item->perihal }}
                                                </p>
                                            @endif

                                        </td>


                                        {{-- Nominal --}}
                                        <td class="whitespace-nowrap px-5 py-4 align-top">

                                            <p class="font-semibold text-gray-900">
                                                Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                                            </p>

                                        </td>


                                        {{-- ======================= --}}
                                        {{-- VERIFIKATOR 1 --}}
                                        {{-- ======================= --}}
                                        <td class="min-w-[210px] px-5 py-4 align-top">

                                            @if ($v1)
                                                @php
                                                    $kodeV1 = $v1->statusVerifikasi?->kode_status;

                                                    $classV1 = match ($kodeV1) {
                                                        'ACC' => 'bg-green-100 text-green-700',
                                                        'REVISI' => 'bg-orange-100 text-orange-700',
                                                        'TOLAK' => 'bg-red-100 text-red-700',
                                                        default => 'bg-gray-100 text-gray-600',
                                                    };
                                                @endphp

                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1
                                                             text-xs font-semibold {{ $classV1 }}">
                                                    {{ $v1->statusVerifikasi?->nama_status ?? '-' }}
                                                </span>

                                                <p class="mt-2 text-sm font-semibold text-gray-800">
                                                    {{ $v1->verifikator?->nama_lengkap ?? '-' }}
                                                </p>

                                                @if ($v1->tanggal_verifikasi)
                                                    <p class="mt-1 text-xs text-gray-400">
                                                        {{ \Carbon\Carbon::parse($v1->tanggal_verifikasi)->format('d M Y H:i') }}
                                                    </p>
                                                @endif

                                                @if ($v1->catatan)
                                                    <div class="mt-2 rounded-lg bg-gray-50 p-2.5">
                                                        <p class="text-xs text-gray-600">
                                                            {{ $v1->catatan }}
                                                        </p>
                                                    </div>
                                                @endif
                                            @else
                                                <span
                                                    class="inline-flex rounded-full
                                                             bg-gray-100 px-2.5 py-1
                                                             text-xs font-medium text-gray-500">
                                                    Belum diverifikasi
                                                </span>
                                            @endif

                                        </td>


                                        {{-- ======================= --}}
                                        {{-- VERIFIKATOR 2 --}}
                                        {{-- ======================= --}}
                                        <td class="min-w-[210px] px-5 py-4 align-top">

                                            @if ($v2)
                                                @php
                                                    $kodeV2 = $v2->statusVerifikasi?->kode_status;

                                                    $classV2 = match ($kodeV2) {
                                                        'ACC' => 'bg-green-100 text-green-700',
                                                        'REVISI' => 'bg-orange-100 text-orange-700',
                                                        'TOLAK' => 'bg-red-100 text-red-700',
                                                        default => 'bg-gray-100 text-gray-600',
                                                    };
                                                @endphp

                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1
                                                             text-xs font-semibold {{ $classV2 }}">
                                                    {{ $v2->statusVerifikasi?->nama_status ?? '-' }}
                                                </span>

                                                <p class="mt-2 text-sm font-semibold text-gray-800">
                                                    {{ $v2->verifikator?->nama_lengkap ?? '-' }}
                                                </p>

                                                @if ($v2->tanggal_verifikasi)
                                                    <p class="mt-1 text-xs text-gray-400">
                                                        {{ \Carbon\Carbon::parse($v2->tanggal_verifikasi)->format('d M Y H:i') }}
                                                    </p>
                                                @endif

                                                @if ($v2->catatan)
                                                    <div class="mt-2 rounded-lg bg-gray-50 p-2.5">
                                                        <p class="text-xs text-gray-600">
                                                            {{ $v2->catatan }}
                                                        </p>
                                                    </div>
                                                @endif
                                            @else
                                                <span
                                                    class="inline-flex rounded-full
                                                             bg-gray-100 px-2.5 py-1
                                                             text-xs font-medium text-gray-500">
                                                    Belum diverifikasi
                                                </span>
                                            @endif

                                        </td>


                                        {{-- ======================= --}}
                                        {{-- VERIFIKATOR 3 --}}
                                        {{-- ======================= --}}
                                        <td class="min-w-[210px] px-5 py-4 align-top">

                                            @if ($v3)
                                                @php
                                                    $kodeV3 = $v3->statusVerifikasi?->kode_status;

                                                    $classV3 = match ($kodeV3) {
                                                        'ACC' => 'bg-green-100 text-green-700',
                                                        'REVISI' => 'bg-orange-100 text-orange-700',
                                                        'TOLAK' => 'bg-red-100 text-red-700',
                                                        default => 'bg-gray-100 text-gray-600',
                                                    };
                                                @endphp

                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1
                                                             text-xs font-semibold {{ $classV3 }}">
                                                    {{ $v3->statusVerifikasi?->nama_status ?? '-' }}
                                                </span>

                                                <p class="mt-2 text-sm font-semibold text-gray-800">
                                                    {{ $v3->verifikator?->nama_lengkap ?? '-' }}
                                                </p>

                                                @if ($v3->tanggal_verifikasi)
                                                    <p class="mt-1 text-xs text-gray-400">
                                                        {{ \Carbon\Carbon::parse($v3->tanggal_verifikasi)->format('d M Y H:i') }}
                                                    </p>
                                                @endif

                                                @if ($v3->catatan)
                                                    <div class="mt-2 rounded-lg bg-gray-50 p-2.5">
                                                        <p class="text-xs text-gray-600">
                                                            {{ $v3->catatan }}
                                                        </p>
                                                    </div>
                                                @endif
                                            @else
                                                <span
                                                    class="inline-flex rounded-full
                                                             bg-gray-100 px-2.5 py-1
                                                             text-xs font-medium text-gray-500">
                                                    Belum diverifikasi
                                                </span>
                                            @endif

                                        </td>


                                        {{-- ======================= --}}
                                        {{-- AKSI --}}
                                        {{-- ======================= --}}
                                        <td class="whitespace-nowrap px-5 py-4 text-center align-top">

                                            <a href="{{ route('ppk.pengajuan.show', $item->id_pengajuan) }}"
                                                class="inline-flex items-center justify-center
                                                       gap-2 rounded-lg bg-purple-600
                                                       px-4 py-2.5 text-sm font-semibold
                                                       text-white shadow-sm transition
                                                       hover:bg-purple-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.5 4-5 7-9 7s-7.5-3-9-7c1.5-4 5-7 9-7s7.5 3 9 7z" />
                                                </svg>

                                                Proses PPK
                                            </a>

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="px-6 py-16 text-center">

                        <div
                            class="mx-auto flex h-14 w-14 items-center
                                    justify-center rounded-full
                                    bg-purple-100 text-purple-600">

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>

                        </div>

                        <h4 class="mt-4 text-lg font-semibold text-gray-900">
                            Tidak Ada Pengajuan
                        </h4>

                        <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                            Belum ada pengajuan yang telah menyelesaikan proses
                            Verifikator 1, Verifikator 2, dan Verifikator 3
                            untuk diproses oleh PPK.
                        </p>

                    </div>

                @endif

            </div>

        </div>
    </div>
</x-app-layout>
