<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-purple-600">
                POV 3 — Pencairan
            </p>

            <h2 class="text-2xl font-bold text-gray-900">
                Dashboard PPSPM
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Pengajuan yang telah selesai diproses oleh Bendahara
                dan menunggu proses PPSPM.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Statistik --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Menunggu Proses PPSPM
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0
                                       11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        Pengajuan yang telah selesai diproses Bendahara.
                    </p>

                </div>

            </div>


            {{-- Menu Proses --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        Pengajuan Menunggu PPSPM
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Periksa pengajuan sebelum menentukan keputusan akhir PPSPM.
                    </p>
                </div>


                <div class="p-6">

                    @if ($totalMenunggu > 0)
                        <div
                            class="flex flex-col gap-4
                                   sm:flex-row sm:items-center
                                   sm:justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    Ada {{ $totalMenunggu }} pengajuan menunggu.
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Buka daftar pengajuan untuk melanjutkan proses.
                                </p>
                            </div>

                            <a href="{{ route('ppspm.pengajuan.index') }}"
                                class="inline-flex items-center justify-center
                                       rounded-lg bg-purple-600
                                       px-5 py-3 text-sm font-semibold
                                       text-white shadow-sm
                                       transition hover:bg-purple-700">
                                Lihat Pengajuan
                            </a>
                        </div>
                    @else
                        <div class="rounded-xl bg-gray-50 px-6 py-12 text-center">

                            <div
                                class="mx-auto flex h-12 w-12
                                       items-center justify-center
                                       rounded-full bg-purple-100
                                       text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <h4 class="mt-4 font-semibold text-gray-900">
                                Tidak Ada Pengajuan
                            </h4>

                            <p class="mt-1 text-sm text-gray-500">
                                Belum ada pengajuan yang masuk ke tahap PPSPM.
                            </p>

                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
