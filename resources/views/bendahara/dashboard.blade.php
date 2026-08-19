<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-purple-600">
                POV 3 — Pencairan
            </p>

            <h2 class="text-xl font-bold text-gray-800">
                Dashboard Bendahara
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Monitoring pengajuan yang masuk ke tahap Bendahara.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Statistik --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">
                        Menunggu Proses Bendahara
                    </p>

                    <p class="mt-2 text-3xl font-bold text-purple-600">
                        {{ $totalMenunggu }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Pengajuan yang telah melewati tahap PPK.
                    </p>
                </div>
            </div>

            {{-- Menu utama --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        Proses Pengajuan Bendahara
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Periksa pengajuan dan lakukan proses pada tahap Bendahara.
                    </p>
                </div>

                <div class="p-6">

                    @if ($totalMenunggu > 0)
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <p class="font-semibold text-gray-800">
                                    Ada {{ $totalMenunggu }} pengajuan menunggu.
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Buka daftar pengajuan untuk melakukan pemeriksaan.
                                </p>
                            </div>

                            <a href="{{ route('bendahara.pengajuan.index') }}"
                                class="inline-flex items-center justify-center
                                       rounded-lg bg-purple-600 px-5 py-3
                                       text-sm font-semibold text-white
                                       shadow-sm transition hover:bg-purple-700">
                                Lihat Pengajuan
                            </a>

                        </div>
                    @else
                        <div class="rounded-xl bg-gray-50 px-6 py-10 text-center">

                            <div
                                class="mx-auto flex h-12 w-12 items-center
                                        justify-center rounded-full
                                        bg-purple-100 text-purple-600">

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
                                Belum ada pengajuan yang menunggu proses Bendahara.
                            </p>

                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
