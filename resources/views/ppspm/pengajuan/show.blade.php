<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-sm font-semibold text-purple-600">
                    POV 3 — Pencairan
                </p>

                <h2 class="text-2xl font-bold text-gray-900">
                    Detail Pengajuan PPSPM
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $pengajuan->no_pengajuan }}
                </p>
            </div>

            <span
                class="inline-flex w-fit rounded-full
                       bg-purple-100 px-3 py-1.5
                       text-xs font-semibold text-purple-700">
                {{ $pengajuan->status?->nama_status ?? 'Proses PPSPM' }}
            </span>

        </div>
    </x-slot>


    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Kembali --}}
            <div class="mb-6">
                <a href="{{ route('ppspm.pengajuan.index') }}"
                    class="inline-flex items-center gap-2
                           rounded-lg border border-gray-200
                           bg-white px-4 py-2 text-sm
                           font-semibold text-gray-700
                           shadow-sm transition hover:text-purple-700">
                    ← Kembali ke Daftar
                </a>
            </div>


            {{-- ================================================= --}}
            {{-- INFORMASI PENGAJUAN --}}
            {{-- ================================================= --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Informasi Pengajuan
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Informasi utama pengajuan pembayaran.
                        </p>
                    </div>


                    <div class="divide-y divide-gray-100 px-6">

                        <div class="py-5">
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Nomor Pengajuan
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $pengajuan->no_pengajuan }}
                            </p>
                        </div>


                        <div class="py-5">
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Pengaju
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $pengajuan->pemohon?->nama_lengkap ?? '-' }}
                            </p>
                        </div>


                        <div class="py-5">
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Tanggal Pengajuan
                            </p>

                            <p class="mt-1 text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d M Y') }}
                            </p>
                        </div>


                        <div class="py-5">
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Perihal
                            </p>

                            <p class="mt-1 text-sm text-gray-700">
                                {{ $pengajuan->perihal ?? '-' }}
                            </p>
                        </div>


                        <div class="py-5">
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Total Nominal
                            </p>

                            <p class="mt-1 text-2xl font-bold text-purple-600">
                                Rp
                                {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}
                            </p>
                        </div>


                        <div class="py-5">
                            <p class="text-xs font-semibold uppercase text-gray-400">
                                Keterangan
                            </p>

                            <div class="mt-2 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">
                                {{ $pengajuan->keterangan ?? '-' }}
                            </div>
                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- HASIL VERIFIKATOR --}}
                {{-- ================================================= --}}
                <div
                    class="overflow-hidden rounded-2xl
                           border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-gray-900">
                            Hasil Verifikasi
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Riwayat Verifikator 1 sampai 3.
                        </p>
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
                                               px-3 py-1 text-xs
                                               font-semibold {{ $badgeClass }}">
                                        {{ $verifikasi->statusVerifikasi?->nama_status ?? '-' }}
                                    </span>

                                </div>


                                @if ($verifikasi->tanggal_verifikasi)
                                    <p class="mt-3 text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($verifikasi->tanggal_verifikasi)->format('d M Y H:i') }}
                                    </p>
                                @endif


                                <div class="mt-4 rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs font-semibold uppercase text-gray-400">
                                        Catatan
                                    </p>

                                    <p class="mt-1 text-sm text-gray-700">
                                        {{ $verifikasi->catatan ?? '-' }}
                                    </p>
                                </div>

                            </div>

                        @empty

                            <div class="rounded-lg bg-gray-50 p-5 text-center">
                                <p class="text-sm text-gray-500">
                                    Data hasil verifikasi belum tersedia.
                                </p>
                            </div>
                        @endforelse

                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- HASIL PPK --}}
            {{-- ================================================= --}}
            <div
                class="mt-6 overflow-hidden rounded-2xl
                       border border-purple-200 bg-white shadow-sm">

                <div class="border-b border-purple-100 bg-purple-50 px-6 py-5">

                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center
                                   justify-center rounded-xl bg-purple-600
                                   font-bold text-white">
                            PPK
                        </div>

                        <div>
                            <h3 class="font-bold text-gray-900">
                                Hasil Proses PPK
                            </h3>

                            <p class="text-xs text-gray-500">
                                Hasil pemeriksaan pada tahap PPK.
                            </p>
                        </div>
                    </div>

                </div>


                <div class="p-6">

                    @if ($ppk)

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Status
                                </p>

                                <p class="mt-2 font-semibold text-purple-700">
                                    {{ $ppk->statusPencairan?->nama_status ?? '-' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Tanggal
                                </p>

                                <p class="mt-2 text-sm text-gray-700">
                                    @if ($ppk->tanggal_proses)
                                        {{ \Carbon\Carbon::parse($ppk->tanggal_proses)->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Catatan
                                </p>

                                <p class="mt-2 text-sm text-gray-700">
                                    {{ $ppk->catatan ?? '-' }}
                                </p>
                            </div>

                        </div>
                    @else
                        <p class="text-sm text-gray-500">
                            Data proses PPK tidak tersedia.
                        </p>

                    @endif

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- HASIL BENDAHARA --}}
            {{-- ================================================= --}}
            <div
                class="mt-6 overflow-hidden rounded-2xl
                       border border-purple-200 bg-white shadow-sm">

                <div class="border-b border-purple-100 bg-purple-50 px-6 py-5">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-11 w-11 items-center justify-center
                                   rounded-xl bg-purple-600
                                   text-sm font-bold text-white">
                            B
                        </div>

                        <div>
                            <h3 class="font-bold text-gray-900">
                                Hasil Proses Bendahara
                            </h3>

                            <p class="text-xs text-gray-500">
                                Hasil pemeriksaan sebelum masuk PPSPM.
                            </p>
                        </div>

                    </div>

                </div>


                <div class="p-6">

                    @if ($bendahara)

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Status
                                </p>

                                <p class="mt-2 font-semibold text-purple-700">
                                    {{ $bendahara->statusPencairan?->nama_status ?? '-' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Tanggal
                                </p>

                                <p class="mt-2 text-sm text-gray-700">
                                    @if ($bendahara->tanggal_proses)
                                        {{ \Carbon\Carbon::parse($bendahara->tanggal_proses)->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400">
                                    Catatan
                                </p>

                                <p class="mt-2 text-sm text-gray-700">
                                    {{ $bendahara->catatan ?? '-' }}
                                </p>
                            </div>

                        </div>
                    @else
                        <p class="text-sm text-gray-500">
                            Data proses Bendahara tidak tersedia.
                        </p>

                    @endif

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- KEPUTUSAN PPSPM --}}
            {{-- ================================================= --}}
            <div class="mt-6 overflow-hidden rounded-2xl
                       border border-gray-200 bg-white shadow-sm"
                x-data="{
                    keputusan: '{{ old('id_status_pencairan', '') }}',
                    submitting: false
                }">

                <div class="border-b border-gray-100 px-6 py-5">

                    <h3 class="text-lg font-bold text-gray-900">
                        Keputusan PPSPM
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Tentukan keputusan akhir terhadap pengajuan pembayaran.
                    </p>

                </div>


                <form
                    action="{{ route('ppspm.pengajuan.keputusan', $pengajuan->id_pengajuan) }}"
                    method="POST" class="p-6"
                    x-on:submit="
                        if (!confirm(
                            'Apakah Anda yakin ingin menyimpan keputusan PPSPM?'
                        )) {
                            $event.preventDefault();
                            return;
                        }

                        submitting = true;
                    ">

                    @csrf


                    {{-- Pilihan --}}
                    <div>

                        <label class="block text-sm font-semibold text-gray-700">
                            Keputusan
                            <span class="text-red-500">*</span>
                        </label>


                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

                            {{-- SESUAI --}}
                            <label class="cursor-pointer rounded-xl border-2 p-5 transition"
                                :class="keputusan == '2' ?
                                    'border-purple-500 bg-purple-50' :
                                    'border-gray-200 hover:border-purple-300'">

                                <input type="radio" name="id_status_pencairan" value="2" x-model="keputusan"
                                    class="sr-only" required>

                                <div class="flex items-start gap-4">

                                    <div
                                        class="flex h-11 w-11 items-center
                                               justify-center rounded-full
                                               bg-green-100 text-green-600">
                                        ✓
                                    </div>

                                    <div>
                                        <p class="font-bold text-gray-900">
                                            Sesuai
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Pengajuan disetujui dan proses
                                            pembayaran dinyatakan selesai.
                                        </p>
                                    </div>

                                </div>

                            </label>


                            {{-- TIDAK SESUAI --}}
                            <label class="cursor-pointer rounded-xl border-2 p-5 transition"
                                :class="keputusan == '3' ?
                                    'border-red-500 bg-red-50' :
                                    'border-gray-200 hover:border-red-300'">

                                <input type="radio" name="id_status_pencairan" value="3" x-model="keputusan"
                                    class="sr-only" required>

                                <div class="flex items-start gap-4">

                                    <div
                                        class="flex h-11 w-11 items-center
                                               justify-center rounded-full
                                               bg-red-100 text-red-600">
                                        ✕
                                    </div>

                                    <div>
                                        <p class="font-bold text-gray-900">
                                            Tidak Sesuai
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Pengajuan tidak dapat diselesaikan
                                            pada tahap PPSPM.
                                        </p>
                                    </div>

                                </div>

                            </label>

                        </div>


                        @error('id_status_pencairan')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Catatan --}}
                    <div class="mt-7">

                        <label for="catatan" class="block text-sm font-semibold text-gray-700">
                            Catatan

                            <span x-show="keputusan == '3'" class="text-red-500">
                                *
                            </span>
                        </label>

                        <p class="mt-1 text-xs text-gray-500">
                            Catatan wajib diisi jika keputusan Tidak Sesuai.
                        </p>


                        <textarea name="catatan" id="catatan" rows="5" :required="keputusan == '3'"
                            class="mt-3 block w-full rounded-xl
                                   border-gray-300 shadow-sm
                                   focus:border-purple-500
                                   focus:ring-purple-500"
                            placeholder="Tuliskan catatan PPSPM...">{{ old('catatan') }}</textarea>


                        @error('catatan')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Tombol --}}
                    <div
                        class="mt-7 flex flex-col gap-4
                               border-t border-gray-100 pt-6
                               sm:flex-row sm:items-center
                               sm:justify-between">

                        <div>
                            <p class="text-xs text-gray-500">
                                Pastikan seluruh tahapan sebelumnya sudah diperiksa.
                            </p>

                            <p x-show="keputusan == '2'" class="mt-1 text-xs font-semibold text-green-600">
                                Pengajuan akan ditandai selesai.
                            </p>
                        </div>


                        <button type="submit" :disabled="!keputusan || submitting"
                            class="inline-flex items-center justify-center
                                   gap-2 rounded-lg bg-purple-600
                                   px-6 py-3 text-sm font-semibold
                                   text-white shadow-sm transition
                                   hover:bg-purple-700
                                   disabled:cursor-not-allowed
                                   disabled:bg-gray-300">

                            <span x-show="!submitting">
                                Simpan Keputusan PPSPM
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
