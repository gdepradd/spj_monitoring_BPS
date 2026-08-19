<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-purple-600">
                    POV 3 — Pencairan
                </p>

                <h2 class="text-2xl font-bold text-gray-900">
                    Daftar Pengajuan PPSPM
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Pengajuan yang telah selesai diproses Bendahara
                    dan menunggu keputusan PPSPM.
                </p>
            </div>

            <a href="{{ route('ppspm.dashboard') }}"
                class="inline-flex items-center justify-center gap-2
                       rounded-lg border border-gray-300 bg-white
                       px-4 py-2.5 text-sm font-semibold text-gray-700
                       shadow-sm transition hover:bg-gray-50">
                ← Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Ringkasan --}}
            <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-2">

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Menunggu PPSPM
                    </p>

                    <p class="mt-2 text-3xl font-bold text-purple-600">
                        {{ $pengajuan->count() }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Pengajuan yang siap diproses oleh PPSPM.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Total Nominal
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        Rp {{ number_format($pengajuan->sum('total_nominal'), 0, ',', '.') }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Total nilai pengajuan yang menunggu keputusan akhir.
                    </p>
                </div>

            </div>


            {{-- Tabel --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        Pengajuan Menunggu Proses PPSPM
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Periksa pengajuan sebelum memberikan keputusan akhir.
                    </p>
                </div>


                @if ($pengajuan->count() > 0)

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        No. Pengajuan
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Pengaju
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Perihal
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Nominal
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                        Status
                                    </th>

                                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>


                            <tbody class="divide-y divide-gray-100 bg-white">

                                @foreach ($pengajuan as $item)
                                    <tr class="transition hover:bg-gray-50">

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="font-semibold text-gray-900">
                                                {{ $item->no_pengajuan }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                            </p>
                                        </td>


                                        <td class="px-5 py-4">
                                            <p class="font-medium text-gray-800">
                                                {{ $item->pemohon?->nama_lengkap ?? '-' }}
                                            </p>
                                        </td>


                                        <td class="max-w-[250px] px-5 py-4">
                                            <p class="truncate text-sm text-gray-700" title="{{ $item->perihal }}">
                                                {{ $item->perihal ?? '-' }}
                                            </p>
                                        </td>


                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="font-semibold text-gray-900">
                                                Rp
                                                {{ number_format($item->total_nominal, 0, ',', '.') }}
                                            </p>
                                        </td>


                                        <td class="px-5 py-4">
                                            <span
                                                class="inline-flex rounded-full
                                                       bg-purple-100 px-3 py-1
                                                       text-xs font-semibold
                                                       text-purple-700">
                                                {{ $item->status?->nama_status ?? 'Proses PPSPM' }}
                                            </span>
                                        </td>


                                        <td class="whitespace-nowrap px-5 py-4 text-center">

                                            <a href="{{ route('ppspm.pengajuan.show', $item->id_pengajuan) }}"
                                                class="inline-flex items-center
                                                       justify-center gap-2
                                                       rounded-lg bg-purple-600
                                                       px-4 py-2.5
                                                       text-sm font-semibold
                                                       text-white shadow-sm
                                                       transition hover:bg-purple-700">
                                                Lihat & Proses
                                            </a>

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-16 text-center">

                        <div
                            class="mx-auto flex h-14 w-14
                                   items-center justify-center
                                   rounded-full bg-purple-100
                                   text-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>

                        <h4 class="mt-4 text-lg font-semibold text-gray-900">
                            Tidak Ada Pengajuan
                        </h4>

                        <p class="mt-2 text-sm text-gray-500">
                            Belum ada pengajuan yang masuk ke tahap PPSPM.
                        </p>

                    </div>

                @endif

            </div>

        </div>
    </div>
</x-app-layout>
