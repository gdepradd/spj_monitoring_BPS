<x-app-layout title="Detail Verifikasi Pengajuan">

    @php
        /*
        |--------------------------------------------------------------------------
        | Ambil riwayat masing-masing tahap verifikasi
        |--------------------------------------------------------------------------
        */

        $verifikasi1 = $pengajuan->verifikasi->where('tahap', 1)->sortByDesc('id_verifikasi')->first();

        $verifikasi2 = $pengajuan->verifikasi->where('tahap', 2)->sortByDesc('id_verifikasi')->first();

        $verifikasi3 = $pengajuan->verifikasi->where('tahap', 3)->sortByDesc('id_verifikasi')->first();

        $urutanVerifikator = (int) auth()->user()->urutan_verifikator;
    @endphp


    <div class="grid gap-6 xl:grid-cols-3">

        {{-- ========================================================= --}}
        {{-- KOLOM UTAMA --}}
        {{-- ========================================================= --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- ===================================================== --}}
            {{-- INFORMASI PENGAJUAN --}}
            {{-- ===================================================== --}}
            <section class="overflow-hidden rounded-xl
                       border border-ui-border bg-ui-card">

                <div
                    class="flex flex-col gap-4 border-b
                           border-ui-border p-6
                           sm:flex-row sm:items-start
                           sm:justify-between">

                    <div>

                        <!-- <p class="text-sm text-ui-muted">
                            Nomor Pengajuan
                        </p> -->
<!-- 
                        <h2 class="mt-1 text-xl font-bold text-gray-900">
                            {{ $pengajuan->no_pengajuan }}
                        </h2> -->

                        <p class="mt-2 text-sm text-ui-muted">
                            Verifikasi Tahap {{ $urutanVerifikator }}
                        </p>

                    </div>


                    @if ($pengajuan->status)
                        <span
                            class="inline-flex w-fit rounded-full
                                   border border-amber-200
                                   bg-amber-50 px-3 py-1
                                   text-xs font-semibold text-amber-700">
                            {{ $pengajuan->status->nama_status }}
                        </span>
                    @endif

                </div>


                <div class="p-6">

                    <dl class="grid gap-6 sm:grid-cols-2">

                        <div>
                            <dt
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Tanggal Pengajuan
                            </dt>

                            <dd class="mt-1.5 text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d M Y') }}
                            </dd>
                        </div>


                        <div>
                            <dt
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Total Nominal
                            </dt>

                            <dd class="mt-1.5 text-lg font-bold text-gray-900">
                                Rp
                                {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}
                            </dd>
                        </div>


                        <div class="sm:col-span-2">
                            <dt
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Perihal
                            </dt>

                            <dd class="mt-1.5 text-sm text-gray-900">
                                {{ $pengajuan->perihal ?? '-' }}
                            </dd>
                        </div>


                        <div class="sm:col-span-2">

                            <dt
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Keterangan
                            </dt>

                            <dd
                                class="mt-2 rounded-lg bg-gray-50
                                       p-4 text-sm leading-relaxed
                                       text-gray-700">
                                {{ $pengajuan->keterangan ?? '-' }}
                            </dd>

                        </div>


                        @if ($pengajuan->catatan_pengaju)
                            <div class="sm:col-span-2">

                                <dt
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-ui-muted">
                                    Catatan Pengaju
                                </dt>

                                <dd
                                    class="mt-2 rounded-lg
                                           border border-orange-200
                                           bg-orange-50 p-4
                                           text-sm text-orange-800">
                                    {{ $pengajuan->catatan_pengaju }}
                                </dd>

                            </div>
                        @endif

                    </dl>

                </div>

            </section>



            {{-- ===================================================== --}}
            {{-- INFORMASI PENGAJU --}}
            {{-- ===================================================== --}}
            <section class="overflow-hidden rounded-xl
                       border border-ui-border bg-ui-card">

                <div class="border-b border-ui-border p-6">

                    <h3 class="font-semibold text-gray-900">
                        Informasi Pengaju
                    </h3>

                    <p class="mt-1 text-sm text-ui-muted">
                        Data pegawai yang mengajukan pembayaran.
                    </p>

                </div>


                <div class="p-6">

                    <div class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Nama Lengkap
                            </p>

                            <p class="mt-1.5 font-semibold text-gray-900">
                                {{ $pengajuan->pemohon?->nama_lengkap ?? '-' }}
                            </p>
                        </div>


                        <div>
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Email
                            </p>

                            <p class="mt-1.5 text-sm text-gray-700">
                                {{ $pengajuan->pemohon?->email ?? '-' }}
                            </p>
                        </div>


                        <div>
                            <!-- <p
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-ui-muted">
                                Nomor HP
                            </p> -->

                            <!-- <p class="mt-1.5 text-sm text-gray-700">
                                {{ $pengajuan->pemohon?->no_hp ?? '-' }}
                            </p> -->
                        </div>

                    </div>

                </div>

            </section>



            {{-- ===================================================== --}}
            {{-- FORM KEPUTUSAN --}}
            {{-- ===================================================== --}}
            <section class="overflow-hidden rounded-xl
                       border border-ui-border bg-ui-card"
                x-data="{
                    keputusan: '{{ old('id_status_verifikasi', '') }}',
                    submitting: false
                }">

                <div class="border-b border-ui-border p-6">

                    <div
                        class="flex flex-col gap-2
                               sm:flex-row sm:items-center
                               sm:justify-between">

                        <div>

                            <h3 class="font-semibold text-gray-900">
                                Keputusan Verifikasi
                            </h3>

                            <p class="mt-1 text-sm text-ui-muted">
                                Berikan keputusan untuk Verifikasi Tahap
                                {{ $urutanVerifikator }}.
                            </p>

                        </div>


                        <span
                            class="inline-flex w-fit rounded-full
                                   bg-green-50 px-3 py-1
                                   text-xs font-semibold text-green-700">
                            Verifikator {{ $urutanVerifikator }}
                        </span>

                    </div>

                </div>


                <form action="{{ route('verifikator.pengajuan.keputusan', $pengajuan->id_pengajuan) }}" method="POST" onsubmit="konfirmasiKeputusan(event, this)">

                    @csrf


                    {{-- ============================================= --}}
                    {{-- PILIHAN KEPUTUSAN --}}
                    {{-- ============================================= --}}
                    <div>

                        <label class="text-sm font-semibold text-gray-700">
                            Pilih Keputusan
                            <span class="text-red-500">*</span>
                        </label>


                        <div class="mt-4 grid gap-4 md:grid-cols-3">

                            {{-- ===================================== --}}
                            {{-- ACC = ID 2 --}}
                            {{-- ===================================== --}}
                            <label
                                class="cursor-pointer rounded-xl
                                       border-2 p-5 transition"
                                :class="keputusan == '2' ?
                                    'border-green-500 bg-green-50' :
                                    'border-gray-200 bg-white hover:border-green-300'">

                                <input type="radio" name="id_status_verifikasi" value="2" x-model="keputusan"
                                    class="sr-only" required>


                                <div class="flex items-start gap-3">

                                    <div
                                        class="flex h-10 w-10
                                               flex-shrink-0 items-center
                                               justify-center rounded-full
                                               bg-green-100
                                               font-bold text-green-600">
                                        ✓
                                    </div>


                                    <div>

                                        <p class="font-bold text-gray-900">
                                            ACC
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">

                                            @if ($urutanVerifikator === 3)
                                                Lolos verifikasi dan diteruskan
                                                ke Bendahara.
                                            @else
                                                Lanjut ke tahap verifikasi
                                                berikutnya.
                                            @endif

                                        </p>

                                    </div>

                                </div>

                            </label>



                            {{-- ===================================== --}}
                            {{-- REVISI = ID 3 --}}
                            {{-- ===================================== --}}
                            <label
                                class="cursor-pointer rounded-xl
                                       border-2 p-5 transition"
                                :class="keputusan == '3' ?
                                    'border-orange-500 bg-orange-50' :
                                    'border-gray-200 bg-white hover:border-orange-300'">

                                <input type="radio" name="id_status_verifikasi" value="3" x-model="keputusan"
                                    class="sr-only" required>


                                <div class="flex items-start gap-3">

                                    <div
                                        class="flex h-10 w-10
                                               flex-shrink-0 items-center
                                               justify-center rounded-full
                                               bg-orange-100
                                               font-bold text-orange-600">
                                        !
                                    </div>


                                    <div>

                                        <p class="font-bold text-gray-900">
                                            Revisi
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Kembalikan pengajuan kepada
                                            pegawai untuk diperbaiki.
                                        </p>

                                    </div>

                                </div>

                            </label>



                            {{-- ===================================== --}}
                            {{-- TOLAK = ID 4 --}}
                            {{-- ===================================== --}}
                            <label
                                class="cursor-pointer rounded-xl
                                       border-2 p-5 transition"
                                :class="keputusan == '4' ?
                                    'border-red-500 bg-red-50' :
                                    'border-gray-200 bg-white hover:border-red-300'">

                                <input type="radio" name="id_status_verifikasi" value="4" x-model="keputusan"
                                    class="sr-only" required>


                                <div class="flex items-start gap-3">

                                    <div
                                        class="flex h-10 w-10
                                               flex-shrink-0 items-center
                                               justify-center rounded-full
                                               bg-red-100
                                               font-bold text-red-600">
                                        ×
                                    </div>


                                    <div>

                                        <p class="font-bold text-gray-900">
                                            Tolak
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Pengajuan ditolak dan proses
                                            dihentikan.
                                        </p>

                                    </div>

                                </div>

                            </label>

                        </div>


                        @error('id_status_verifikasi')
                            <p class="mt-3 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>



                    {{-- ============================================= --}}
                    {{-- CATATAN --}}
                    {{-- ============================================= --}}
                    <div class="mt-7">

                        <label for="catatan" class="text-sm font-semibold text-gray-700">
                            Catatan

                            <span
                                x-show="
                                    keputusan == '3'
                                    || keputusan == '4'
                                "
                                class="text-red-500">
                                *
                            </span>
                        </label>


                        <p class="mt-1 text-xs text-ui-muted">

                            Catatan wajib diisi apabila keputusan
                            <strong>Revisi</strong> atau
                            <strong>Tolak</strong>.

                        </p>


                        <textarea id="catatan" name="catatan" rows="5"
                            :required="keputusan == '3' ||
                                keputusan == '4'"
                            class="mt-3 block w-full
                                   rounded-lg border-gray-300
                                   shadow-sm
                                   focus:border-green-500
                                   focus:ring-green-500"
                            placeholder="Tuliskan catatan verifikasi...">{{ old('catatan') }}</textarea>


                        @error('catatan')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>



                    {{-- ============================================= --}}
                    {{-- INFORMASI ALUR --}}
                    {{-- ============================================= --}}
                    <div x-show="keputusan" class="mt-6 rounded-lg border p-4"
                        :class="{
                            'border-green-200 bg-green-50': keputusan == '2',
                        
                            'border-orange-200 bg-orange-50': keputusan == '3',
                        
                            'border-red-200 bg-red-50': keputusan == '4'
                        }">

                        <template x-if="keputusan == '2'">

                            <p class="text-sm text-green-700">

                                @if ($urutanVerifikator === 1)
                                    Setelah disetujui, pengajuan akan
                                    diteruskan ke
                                    <strong>Verifikator 2</strong>.
                                @elseif ($urutanVerifikator === 2)
                                    Setelah disetujui, pengajuan akan
                                    diteruskan ke
                                    <strong>Verifikator 3</strong>.
                                @else
                                    Setelah Verifikator 3 menyetujui,
                                    pengajuan akan masuk ke
                                    <strong>Menunggu Pencairan</strong>
                                    dan diteruskan ke
                                    <strong>Bendahara</strong> untuk
                                    menentukan metode pembayaran.
                                @endif

                            </p>

                        </template>


                        <template x-if="keputusan == '3'">

                            <p class="text-sm text-orange-700">
                                Pengajuan akan dikembalikan kepada
                                pegawai untuk diperbaiki.
                            </p>

                        </template>


                        <template x-if="keputusan == '4'">

                            <p class="text-sm text-red-700">
                                Pengajuan akan berstatus
                                <strong>Ditolak</strong> dan tidak
                                diteruskan ke tahap berikutnya.
                            </p>

                        </template>

                    </div>



                    {{-- ============================================= --}}
                    {{-- TOMBOL --}}
                    {{-- ============================================= --}}
                    <div
                        class="mt-7 flex flex-col gap-4
                               border-t border-ui-border pt-6
                               sm:flex-row sm:items-center
                               sm:justify-between">

                        <a href="{{ route('verifikator.pengajuan.index') }}"
                            class="inline-flex items-center
                                   justify-center rounded-lg
                                   border border-gray-300
                                   bg-white px-4 py-2.5
                                   text-sm font-semibold
                                   text-gray-700 transition
                                   hover:bg-gray-50">
                            ← Kembali
                        </a>


                        <button type="submit" :disabled="!keputusan || submitting"
                            class="inline-flex items-center
                                   justify-center rounded-lg
                                   bg-green-600 px-6 py-2.5
                                   text-sm font-semibold text-white
                                   shadow-sm transition
                                   hover:bg-green-700
                                   disabled:cursor-not-allowed
                                   disabled:bg-gray-300">

                            <span x-show="!submitting">
                                Simpan Keputusan
                            </span>

                            <span x-show="submitting">
                                Menyimpan...
                            </span>

                        </button>

                    </div>

                </form>

            </section>

        </div>



        {{-- ========================================================= --}}
        {{-- TIMELINE --}}
        {{-- ========================================================= --}}
        <aside>

            <div class="rounded-xl border border-ui-border
                       bg-ui-card p-6">

                <div>

                    <h3 class="font-semibold text-gray-900">
                        Timeline Verifikasi
                    </h3>

                    <p class="mt-1 text-xs text-ui-muted">
                        Riwayat proses verifikasi pengajuan.
                    </p>

                </div>


                <div class="mt-6">

                    {{-- ============================================= --}}
                    {{-- PENGAJUAN DIBUAT --}}
                    {{-- ============================================= --}}
                    <x-timeline-item label="Pengajuan Dibuat" status="SELESAI" :aktif="false" :waktu="$pengajuan->created_at"
                        :catatan="$pengajuan->catatan_pengaju" />


                    {{-- ============================================= --}}
                    {{-- VERIFIKATOR 1 --}}
                    {{-- ============================================= --}}
                    <x-timeline-item :label="'Verifikator 1' .
                        ($verifikasi1?->verifikator ? ' (' . $verifikasi1->verifikator->nama_lengkap . ')' : '')" :status="$verifikasi1?->statusVerifikasi" :aktif="$pengajuan->isTahapAktif('VERIFIKASI_1') ||
                        ($pengajuan->isTahapAktif('DIAJUKAN') && !$verifikasi1)" :waktu="$verifikasi1?->tanggal_verifikasi"
                        :catatan="$verifikasi1?->catatan" />


                    {{-- ============================================= --}}
                    {{-- VERIFIKATOR 2 --}}
                    {{-- ============================================= --}}
                    <x-timeline-item :label="'Verifikator 2' .
                        ($verifikasi2?->verifikator ? ' (' . $verifikasi2->verifikator->nama_lengkap . ')' : '')" :status="$verifikasi2?->statusVerifikasi" :aktif="$pengajuan->isTahapAktif('VERIFIKASI_2')" :waktu="$verifikasi2?->tanggal_verifikasi"
                        :catatan="$verifikasi2?->catatan" />


                    {{-- ============================================= --}}
                    {{-- VERIFIKATOR 3 --}}
                    {{-- ============================================= --}}
                    <x-timeline-item :label="'Verifikator 3' .
                        ($verifikasi3?->verifikator ? ' (' . $verifikasi3->verifikator->nama_lengkap . ')' : '')" :status="$verifikasi3?->statusVerifikasi" :aktif="$pengajuan->isTahapAktif('VERIFIKASI_3')" :waktu="$verifikasi3?->tanggal_verifikasi"
                        :catatan="$verifikasi3?->catatan" />

                </div>

            </div>

        </aside>

    </div>

</x-app-layout>
