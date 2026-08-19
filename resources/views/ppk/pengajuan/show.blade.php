<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-purple-600">
                POV 3 — Pencairan
            </p>

            <h2 class="text-xl font-bold text-gray-800">
                Detail Pengajuan PPK
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $pengajuan->no_pengajuan }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- tombol kembali --}}
            <div class="mb-5">
                <a href="{{ route('ppk.pengajuan.index') }}"
                    class="inline-flex items-center text-sm font-semibold
                           text-purple-600 hover:text-purple-700">
                    ← Kembali ke daftar pengajuan
                </a>
            </div>


            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- DATA PENGAJUAN --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Informasi Pengajuan
                        </h3>
                    </div>

                    <div class="space-y-5 p-6">

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                No. Pengajuan
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $pengajuan->no_pengajuan }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Pengaju
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $pengajuan->pemohon?->nama_lengkap ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Tanggal Pengajuan
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d M Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Perihal
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $pengajuan->perihal }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Total Nominal
                            </p>

                            <p class="mt-1 text-2xl font-bold text-purple-600">
                                Rp
                                {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Keterangan
                            </p>

                            <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm text-gray-700">
                                {{ $pengajuan->keterangan ?? '-' }}
                            </div>
                        </div>

                    </div>

                </div>


                {{-- HASIL VERIFIKASI --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Hasil Verifikasi
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Riwayat pemeriksaan Verifikator 1 sampai 3.
                        </p>
                    </div>


                    <div class="space-y-4 p-6">

                        @forelse($pengajuan->verifikasi->sortBy('tahap') as $verifikasi)
                            <div class="rounded-xl border border-gray-200 p-5">

                                <div class="flex items-start justify-between gap-4">

                                    <div>
                                        <p class="font-bold text-gray-900">
                                            Verifikator {{ $verifikasi->tahap }}
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $verifikasi->verifikator?->nama_lengkap ?? '-' }}
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex rounded-full
                                                 bg-green-100 px-3 py-1
                                                 text-xs font-semibold text-green-700">

                                        {{ $verifikasi->statusVerifikasi?->nama_status ?? '-' }}

                                    </span>

                                </div>


                                <div class="mt-4 border-t border-gray-100 pt-4">

                                    <p class="text-xs font-semibold uppercase text-gray-400">
                                        Tanggal Verifikasi
                                    </p>

                                    <p class="mt-1 text-sm text-gray-700">
                                        @if ($verifikasi->tanggal_verifikasi)
                                            {{ \Carbon\Carbon::parse($verifikasi->tanggal_verifikasi)->format('d M Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </p>


                                    <p class="mt-4 text-xs font-semibold uppercase text-gray-400">
                                        Catatan
                                    </p>

                                    <div class="mt-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                                        {{ $verifikasi->catatan ?? '-' }}
                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="rounded-lg bg-gray-50 p-5 text-center">
                                <p class="text-sm text-gray-500">
                                    Belum ada data hasil verifikasi.
                                </p>
                            </div>
                        @endforelse

                    </div>

                </div>

            </div>


            {{-- PROSES PPK --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-100 px-6 py-5">

                    <h3 class="text-lg font-bold text-gray-900">
                        Keputusan PPK
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Tentukan hasil pemeriksaan pengajuan pada tahap PPK.
                    </p>

                </div>


                <div class="p-6">

                    <form
                        action="{{ route('ppk.pengajuan.keputusan', $pengajuan->id_pengajuan) }}"
                        method="POST">

                        @csrf


                        <div class="mb-5">

                            <label class="block text-sm font-semibold text-gray-700">
                                Keputusan
                            </label>

                            <div class="mt-3 flex flex-wrap gap-5">

                                <label class="inline-flex items-center">
                                    <input type="radio" name="id_status_pencairan" value="2"
                                        class="text-purple-600" required>

                                    <span class="ml-2">
                                        Sesuai
                                    </span>
                                </label>


                                <label class="inline-flex items-center">
                                    <input type="radio" name="id_status_pencairan" value="3" class="text-red-600"
                                        required>

                                    <span class="ml-2">
                                        Tidak Sesuai
                                    </span>
                                </label>

                            </div>

                        </div>


                        <div class="mb-5">

                            <label for="catatan" class="block text-sm font-semibold text-gray-700">
                                Catatan
                            </label>

                            <textarea name="catatan" id="catatan" rows="4"
                                class="mt-2 w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-purple-500
                                       focus:ring-purple-500"
                                placeholder="Masukkan catatan PPK..."></textarea>

                        </div>


                        <div class="flex justify-end">

                            <button type="submit"
                                class="rounded-lg bg-purple-600
                                       px-6 py-3 text-sm font-semibold
                                       text-white shadow-sm transition
                                       hover:bg-purple-700">
                                Simpan Keputusan PPK
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
