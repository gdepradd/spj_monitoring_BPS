<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-purple-600">
                POV 3 — Pencairan
            </p>

            <h2 class="text-xl font-bold text-gray-800">
                Daftar Pengajuan PPK
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Pengajuan yang telah selesai melalui tahap verifikasi.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        Pengajuan Menunggu PPK
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Periksa hasil Verifikator 1, 2, dan 3 sebelum memproses pengajuan.
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
                                        Tanggal
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
                                    <tr class="hover:bg-gray-50">

                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="font-semibold text-gray-900">
                                                {{ $item->no_pengajuan }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            {{ $item->pemohon?->nama_lengkap ?? '-' }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-800">
                                            Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span
                                                class="inline-flex rounded-full bg-purple-100 px-3 py-1
                                                         text-xs font-semibold text-purple-700">
                                                {{ $item->status?->nama_status ?? 'Proses PPK' }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-center">
                                            <a href="{{ route('ppk.pengajuan.show', $item->id_pengajuan) }}"
                                                class="inline-flex items-center rounded-lg bg-purple-600
                                                       px-4 py-2 text-sm font-semibold text-white
                                                       shadow-sm transition hover:bg-purple-700">
                                                Lihat Detail
                                            </a>
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <h4 class="font-semibold text-gray-900">
                            Belum Ada Pengajuan
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Belum ada pengajuan yang masuk ke tahap PPK.
                        </p>
                    </div>

                @endif

            </div>

        </div>
    </div>
</x-app-layout>
