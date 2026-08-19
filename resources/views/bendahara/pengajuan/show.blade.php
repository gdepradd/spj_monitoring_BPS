<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-purple-600">
                    POV 3 — Pencairan
                </p>

                <h2 class="mt-1 text-2xl font-bold text-gray-900">
                    Detail Pengajuan Bendahara
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $pengajuan->no_pengajuan }}
                </p>
            </div>

            <span
                class="inline-flex w-fit items-center rounded-full
                       bg-purple-100 px-3 py-1.5
                       text-xs font-semibold text-purple-700">
                {{ $pengajuan->status?->nama_status ?? 'Proses Bendahara' }}
            </span>
        </div>
    </x-slot>


    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- ===================================================== --}}
            {{-- FLASH MESSAGE --}}
            {{-- ===================================================== --}}
            @if (session('success'))
                <div
                    class="mb-6 rounded-xl border border-green-200
                           bg-green-50 px-5 py-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif


            {{-- ===================================================== --}}
            {{-- NAVIGASI --}}
            {{-- ===================================================== --}}
            <div class="mb-6">
                <a href="{{ route('bendahara.pengajuan.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg
                           border border-gray-200 bg-white
                           px-4 py-2 text-sm font-semibold
                           text-gray-700 shadow-sm transition
                           hover:border-purple-300
                           hover:text-purple-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    Kembali ke Daftar
                </a>
            </div>


            {{-- ===================================================== --}}
            {{-- RINGKASAN PENGAJUAN --}}
            {{-- ===================================================== --}}
            <div
                class="mb-6 overflow-hidden rounded-2xl
                       border border-purple-100
                       bg-gradient-to-r from-purple-50 to-white
                       shadow-sm">
                <div class="p-6">
                    <div
                        class="flex flex-col gap-5
                               lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wider text-purple-600">
                                Nomor Pengajuan
                            </p>

                            <h3 class="mt-1 text-xl font-bold text-gray-900">
                                {{ $pengajuan->no_pengajuan }}
                            </h3>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ $pengajuan->perihal ?? 'Tidak ada perihal' }}
                            </p>
                        </div>

                        <div class="lg:text-right">
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wider text-gray-400">
                                Total Nominal
                            </p>

                            <p class="mt-1 text-2xl font-bold text-purple-600">
                                Rp
                                {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ===================================================== --}}
            {{-- DATA PENGAJUAN + VERIFIKASI --}}
            {{-- ===================================================== --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- ================================================= --}}
                {{-- INFORMASI PENGAJUAN --}}
                {{-- ================================================= --}}
                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10 items-center
                                       justify-center rounded-xl
                                       bg-purple-100 text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2
                                           0 01-2-2V5a2 2 0 012-2h5l5
                                           5v11a2 2 0 01-2 2z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="font-bold text-gray-900">
                                    Informasi Pengajuan
                                </h3>

                                <p class="text-xs text-gray-500">
                                    Data utama pengajuan pembayaran.
                                </p>
                            </div>

                        </div>
                    </div>


                    <div class="divide-y divide-gray-100 px-6">

                        {{-- Pengaju --}}
                        <div class="flex justify-between gap-5 py-5">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400">
                                    Pengaju
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $pengajuan->pemohon?->nama_lengkap ?? '-' }}
                                </p>
                            </div>
                        </div>


                        {{-- Tanggal --}}
                        <div class="flex justify-between gap-5 py-5">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400">
                                    Tanggal Pengajuan
                                </p>

                                <p class="mt-1 text-sm text-gray-700">
                                    @if ($pengajuan->tanggal_pengajuan)
                                        {{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>


                        {{-- Perihal --}}
                        <div class="py-5">
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-gray-400">
                                Perihal
                            </p>

                            <p class="mt-1 text-sm leading-relaxed text-gray-700">
                                {{ $pengajuan->perihal ?? '-' }}
                            </p>
                        </div>


                        {{-- Nominal --}}
                        <div class="py-5">
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-gray-400">
                                Total Nominal
                            </p>

                            <p class="mt-1 text-xl font-bold text-purple-600">
                                Rp
                                {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}
                            </p>
                        </div>


                        {{-- Keterangan --}}
                        <div class="py-5">
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-gray-400">
                                Keterangan
                            </p>

                            <div
                                class="mt-2 rounded-xl bg-gray-50
                                       p-4 text-sm leading-relaxed
                                       text-gray-700">
                                {{ $pengajuan->keterangan ?? '-' }}
                            </div>
                        </div>


                        {{-- Catatan Pengaju --}}
                        <div class="py-5">
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-gray-400">
                                Catatan Pengaju
                            </p>

                            <div
                                class="mt-2 rounded-xl bg-gray-50
                                       p-4 text-sm leading-relaxed
                                       text-gray-700">
                                {{ $pengajuan->catatan_pengaju ?? '-' }}
                            </div>
                        </div>

                    </div>
                </div>


                {{-- ================================================= --}}
                {{-- HASIL VERIFIKASI --}}
                {{-- ================================================= --}}
                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10 items-center
                                       justify-center rounded-xl
                                       bg-green-100 text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="font-bold text-gray-900">
                                    Hasil Verifikasi
                                </h3>

                                <p class="text-xs text-gray-500">
                                    Riwayat Verifikator 1 sampai 3.
                                </p>
                            </div>

                        </div>
                    </div>


                    <div class="space-y-4 p-6">

                        @forelse ($pengajuan->verifikasi->sortBy('tahap')
                            as $verifikasi)
                            @php
                                $kode = $verifikasi->statusVerifikasi?->kode_status;

                                $badgeClass = match ($kode) {
                                    'ACC' => 'bg-green-100 text-green-700',

                                    'REVISI' => 'bg-orange-100 text-orange-700',

                                    'TOLAK' => 'bg-red-100 text-red-700',

                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp


                            <div
                                class="rounded-xl border border-gray-200
                                       p-5 transition hover:border-gray-300">
                                <div
                                    class="flex flex-col gap-3
                                           sm:flex-row sm:items-start
                                           sm:justify-between">

                                    <div>
                                        <p class="font-bold text-gray-900">
                                            Verifikator
                                            {{ $verifikasi->tahap }}
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $verifikasi->verifikator?->nama_lengkap ?? '-' }}
                                        </p>
                                    </div>


                                    <span
                                        class="inline-flex w-fit rounded-full
                                               px-3 py-1 text-xs
                                               font-semibold
                                               {{ $badgeClass }}">
                                        {{ $verifikasi->statusVerifikasi?->nama_status ?? '-' }}
                                    </span>

                                </div>


                                @if ($verifikasi->tanggal_verifikasi)
                                    <p class="mt-3 text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($verifikasi->tanggal_verifikasi)->format('d M Y H:i') }}
                                    </p>
                                @endif


                                <div class="mt-4 rounded-lg bg-gray-50 p-3">
                                    <p
                                        class="text-xs font-semibold uppercase
                                               tracking-wide text-gray-400">
                                        Catatan
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-relaxed
                                               text-gray-700">
                                        {{ $verifikasi->catatan ?? '-' }}
                                    </p>
                                </div>

                            </div>

                        @empty

                            <div
                                class="rounded-xl bg-gray-50
                                       px-5 py-10 text-center">
                                <p class="text-sm text-gray-500">
                                    Data hasil verifikasi belum tersedia.
                                </p>
                            </div>
                        @endforelse

                    </div>
                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- HASIL PPK --}}
            {{-- ===================================================== --}}
            @php
                $dataPpk = $ppk ?? null;
            @endphp

            <div
                class="mt-6 overflow-hidden rounded-2xl
                       border border-purple-200 bg-white shadow-sm">

                <div class="border-b border-purple-100
                           bg-purple-50 px-6 py-5">
                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-11 w-11 items-center
                                   justify-center rounded-xl
                                   bg-purple-600 text-sm
                                   font-bold text-white">
                            PPK
                        </div>

                        <div>
                            <h3 class="font-bold text-gray-900">
                                Hasil Proses PPK
                            </h3>

                            <p class="text-xs text-gray-500">
                                Keputusan tahap sebelumnya.
                            </p>
                        </div>

                    </div>
                </div>


                <div class="p-6">

                    @if ($dataPpk)

                        <div class="grid grid-cols-1 gap-5
                                   sm:grid-cols-3">

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400">
                                    Status
                                </p>

                                <span
                                    class="mt-2 inline-flex rounded-full
                                           bg-green-100 px-3 py-1
                                           text-xs font-semibold
                                           text-green-700">
                                    {{ $dataPpk->statusPencairan?->nama_status ?? '-' }}
                                </span>
                            </div>


                            <div>
                                <p
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400">
                                    Tanggal Proses
                                </p>

                                <p class="mt-2 text-sm text-gray-700">
                                    @if ($dataPpk->tanggal_proses)
                                        {{ \Carbon\Carbon::parse($dataPpk->tanggal_proses)->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>


                            <div>
                                <p
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400">
                                    Catatan
                                </p>

                                <p
                                    class="mt-2 text-sm leading-relaxed
                                           text-gray-700">
                                    {{ $dataPpk->catatan ?? '-' }}
                                </p>
                            </div>

                        </div>
                    @else
                        <div class="rounded-xl bg-gray-50
                                   px-5 py-6 text-center">
                            <p class="text-sm text-gray-500">
                                Data proses PPK tidak tersedia.
                            </p>
                        </div>

                    @endif

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- FORM KEPUTUSAN BENDAHARA --}}
            {{-- ===================================================== --}}
            <div class="mt-6 overflow-hidden rounded-2xl
                       border border-gray-200 bg-white shadow-sm"
                x-data="{
                    keputusan: '{{ old('id_status_pencairan', '') }}',
                    submitting: false
                }">

                {{-- Header --}}
                <div class="border-b border-gray-100 px-6 py-5">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center
                                   justify-center rounded-xl
                                   bg-purple-100 text-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9
                                       0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="font-bold text-gray-900">
                                Keputusan Bendahara
                            </h3>

                            <p class="text-xs text-gray-500">
                                Tentukan hasil pemeriksaan pengajuan.
                            </p>
                        </div>

                    </div>

                </div>


                <form
                    action="{{ route('bendahara.pengajuan.keputusan', $pengajuan->id_pengajuan) }}"
                    method="POST" class="p-6"
                    x-on:submit="
                        if (!confirm(
                            'Apakah Anda yakin ingin menyimpan keputusan ini?'
                        )) {
                            $event.preventDefault();
                            return;
                        }

                        submitting = true;
                    ">

                    @csrf


                    {{-- ============================================= --}}
                    {{-- PILIHAN KEPUTUSAN --}}
                    {{-- ============================================= --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">
                            Keputusan
                            <span class="text-red-500">*</span>
                        </label>

                        <p class="mt-1 text-xs text-gray-500">
                            Pilih hasil pemeriksaan Bendahara.
                        </p>


                        <div class="mt-4 grid grid-cols-1 gap-4
                                   md:grid-cols-2">

                            {{-- SESUAI --}}
                            <label
                                class="relative cursor-pointer
                                       rounded-xl border-2 p-5
                                       transition"
                                :class="keputusan == '2' ?
                                    'border-purple-500 bg-purple-50' :
                                    'border-gray-200 bg-white hover:border-purple-300'">
                                <input type="radio" name="id_status_pencairan" value="2" x-model="keputusan"
                                    class="sr-only" required>

                                <div class="flex items-start gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0
                                               items-center justify-center
                                               rounded-full
                                               bg-green-100
                                               text-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>


                                    <div>
                                        <p class="font-bold text-gray-900">
                                            Sesuai
                                        </p>

                                        <p
                                            class="mt-1 text-sm
                                                   leading-relaxed
                                                   text-gray-500">
                                            Pengajuan dinyatakan sesuai
                                            dan dapat dilanjutkan ke
                                            tahap PPSPM.
                                        </p>
                                    </div>

                                </div>


                                <div x-show="keputusan == '2'"
                                    class="absolute right-4 top-4
                                           text-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0
                                               010 1.414l-8 8a1 1
                                               0 01-1.414 0l-4-4a1
                                               1 0 011.414-1.414L8
                                               12.586l7.293-7.293a1
                                               1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>

                            </label>


                            {{-- TIDAK SESUAI --}}
                            <label
                                class="relative cursor-pointer
                                       rounded-xl border-2 p-5
                                       transition"
                                :class="keputusan == '3' ?
                                    'border-red-500 bg-red-50' :
                                    'border-gray-200 bg-white hover:border-red-300'">
                                <input type="radio" name="id_status_pencairan" value="3" x-model="keputusan"
                                    class="sr-only" required>

                                <div class="flex items-start gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0
                                               items-center justify-center
                                               rounded-full bg-red-100
                                               text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>


                                    <div>
                                        <p class="font-bold text-gray-900">
                                            Tidak Sesuai
                                        </p>

                                        <p
                                            class="mt-1 text-sm
                                                   leading-relaxed
                                                   text-gray-500">
                                            Pengajuan tidak dapat
                                            dilanjutkan dan membutuhkan
                                            tindak lanjut.
                                        </p>
                                    </div>

                                </div>


                                <div x-show="keputusan == '3'"
                                    class="absolute right-4 top-4
                                           text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0
                                               010 1.414l-8 8a1 1
                                               0 01-1.414 0l-4-4a1
                                               1 0 011.414-1.414L8
                                               12.586l7.293-7.293a1
                                               1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>

                            </label>

                        </div>


                        @error('id_status_pencairan')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ============================================= --}}
                    {{-- CATATAN --}}
                    {{-- ============================================= --}}
                    <div class="mt-7">

                        <label for="catatan"
                            class="block text-sm font-semibold
                                   text-gray-700">
                            Catatan

                            <span x-show="keputusan == '3'" class="text-red-500">
                                *
                            </span>
                        </label>

                        <p class="mt-1 text-xs text-gray-500">
                            Catatan wajib diisi jika keputusan
                            Tidak Sesuai.
                        </p>


                        <textarea name="catatan" id="catatan" rows="5"
                            class="mt-3 block w-full rounded-xl
                                   border-gray-300 shadow-sm
                                   transition
                                   focus:border-purple-500
                                   focus:ring-purple-500"
                            :required="keputusan == '3'" placeholder="Tuliskan catatan atau hasil pemeriksaan Bendahara...">{{ old('catatan') }}</textarea>


                        @error('catatan')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ============================================= --}}
                    {{-- FOOTER --}}
                    {{-- ============================================= --}}
                    <div
                        class="mt-7 flex flex-col gap-4
                               border-t border-gray-100 pt-6
                               sm:flex-row sm:items-center
                               sm:justify-between">

                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Pastikan seluruh data sudah diperiksa
                                sebelum menyimpan keputusan.
                            </p>

                            <p x-show="keputusan == '2'"
                                class="mt-1 text-xs font-semibold
                                       text-green-600">
                                Pengajuan akan dilanjutkan ke PPSPM.
                            </p>

                            <p x-show="keputusan == '3'"
                                class="mt-1 text-xs font-semibold
                                       text-red-600">
                                Catatan wajib diberikan untuk keputusan
                                Tidak Sesuai.
                            </p>
                        </div>


                        <button type="submit" :disabled="!keputusan || submitting"
                            class="inline-flex items-center
                                   justify-center gap-2 rounded-lg
                                   bg-purple-600 px-6 py-3
                                   text-sm font-semibold text-white
                                   shadow-sm transition
                                   hover:bg-purple-700
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-purple-500
                                   focus:ring-offset-2
                                   disabled:cursor-not-allowed
                                   disabled:bg-gray-300">

                            <svg x-show="!submitting" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>


                            <svg x-show="submitting" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>

                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4
                                       4 0 00-4 4H4z"></path>
                            </svg>


                            <span x-show="!submitting">
                                Simpan Keputusan Bendahara
                            </span>

                            <span x-show="submitting">
                                Menyimpan...
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
