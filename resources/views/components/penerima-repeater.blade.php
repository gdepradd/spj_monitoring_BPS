{{--
    Daftar penerima untuk pencairan parsial.

    Satu pembayaran boleh dibagi ke beberapa orang. Cara bayar, tanggal, dan
    nomor kwitansi berlaku sama untuk seluruh penerima pada pembayaran ini,
    jadi tiap baris cukup berisi nama penerima dan nominalnya.

    - $batas  : nominal maksimum yang boleh dibayarkan (total pengajuan / sisa)
    - $warna  : aksen mengikuti form induk, 'green' (UP/TUP) atau 'blue' (LS)
    - $usulan : daftar penerima dari pengaju, dipakai sebagai isian awal sehingga
                Bendahara tidak perlu mengetik ulang nama dan nominalnya

    Kelas warna ditulis utuh, bukan dirangkai, agar ikut terkompilasi Tailwind.
--}}
@props(['batas' => 0, 'warna' => 'blue', 'usulan' => []])

@php
    $lama = old('penerima');

    // Urutan sumber isian awal: kiriman form yang gagal validasi, lalu usulan
    // pengaju, lalu satu baris kosong. Baris tanpa nama atau bernominal nol
    // dibuang; pemotongan terhadap $batas tetap dilakukan di sisi server
    // (PencairanService::susunPenerima) agar tidak bisa dilewati dari peramban.
    $daftarUsulan = collect($usulan)
        ->map(fn ($baris) => [
            'nama_penerima' => (string) (is_array($baris) ? ($baris['nama_penerima'] ?? '') : $baris->nama_penerima),
            'nominal' => (string) (int) round((float) (is_array($baris) ? ($baris['nominal'] ?? 0) : $baris->nominal)),
        ])
        ->filter(fn ($baris) => $baris['nama_penerima'] !== '' && (float) $baris['nominal'] > 0)
        ->values();

    if (is_array($lama) && $lama !== []) {
        $awal = array_values($lama);
    } elseif ($daftarUsulan->isNotEmpty()) {
        $awal = $daftarUsulan->all();
    } else {
        $awal = [['nama_penerima' => '', 'nominal' => '']];
    }

    $totalUsulan = (float) $daftarUsulan->sum(fn ($baris) => (float) $baris['nominal']);

    $gayaCatatan = $warna === 'green' ? 'text-green-800' : 'text-blue-800';

    $gaya = $warna === 'green'
        ? [
            'kotak'  => 'border-green-200',
            'tombol' => 'border-green-300 text-green-700 hover:bg-green-50',
            'fokus'  => 'focus:border-green-500 focus:ring-green-500',
        ]
        : [
            'kotak'  => 'border-blue-200',
            'tombol' => 'border-blue-300 text-blue-700 hover:bg-blue-50',
            'fokus'  => 'focus:border-blue-500 focus:ring-blue-500',
        ];
@endphp

<div x-data="{
        batas: {{ (float) $batas }},
        penerima: @js($awal),
        tambah() {
            this.penerima.push({ nama_penerima: '', nominal: '' });
        },
        hapus(i) {
            if (this.penerima.length > 1) this.penerima.splice(i, 1);
        },
        /* Buang seluruh usulan dan mulai dari satu baris kosong. */
        kosongkan() {
            this.penerima = [{ nama_penerima: '', nominal: '' }];
        },
        get total() {
            return this.penerima.reduce((jumlah, baris) => jumlah + (parseFloat(baris.nominal) || 0), 0);
        },
        get sisa() {
            return this.batas - this.total;
        },
        rupiah(angka) {
            return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
        },
        /* Sisakan digit saja, lalu buang nol di depan supaya '007' jadi '7'. */
        bersihkan(teks) {
            return String(teks).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        },
        /* Pemisah ribuan untuk kolom nominal; tanpa awalan 'Rp'. */
        format(angka) {
            return angka === '' || angka === null ? '' : Number(angka).toLocaleString('id-ID');
        },
        ketikNominal(baris, peristiwa) {
            baris.nominal = this.bersihkan(peristiwa.target.value);
            peristiwa.target.value = this.format(baris.nominal);
        },
     }"
     class="rounded-lg border bg-white p-3 {{ $gaya['kotak'] }}">

    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm font-semibold text-gray-800">Daftar Penerima</p>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="tambah()"
                    class="rounded-lg border px-3 py-1 text-xs font-semibold {{ $gaya['tombol'] }}">
                + Tambah Penerima
            </button>
            <button type="button" @click="kosongkan()"
                    class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                Kosongkan
            </button>
        </div>
    </div>

    {{-- Daftar sudah terisi dari usulan pengaju. Dikatakan terang-terangan supaya
         Bendahara tahu angka ini bukan ketikannya sendiri dan perlu diperiksa. --}}
    @if($daftarUsulan->isNotEmpty() && ! (is_array($lama) && $lama !== []))
        <p class="mb-3 rounded-lg bg-gray-100 px-3 py-2 text-xs {{ $gayaCatatan }}">
            Terisi otomatis dari daftar penerima yang diajukan pemohon
            ({{ $daftarUsulan->count() }} penerima, Rp {{ number_format($totalUsulan, 0, ',', '.') }}).
            Periksa dan sesuaikan bila perlu sebelum menyimpan.
        </p>
    @endif

    <template x-for="(baris, i) in penerima" :key="i">
        <div class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500" x-text="'Penerima ' + (i + 1)"></span>
                <button type="button" @click="hapus(i)" x-show="penerima.length > 1"
                        class="text-xs font-semibold text-red-600 hover:underline">
                    Hapus
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700">Nama Penerima</label>
                    <input type="text" x-model="baris.nama_penerima"
                           :name="'penerima[' + i + '][nama_penerima]'" required
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm {{ $gaya['fokus'] }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700">Nominal</label>

                    {{-- Kolom yang dilihat Bendahara: teks berpemisah ribuan, tanpa
                         tombol panah dan tidak bisa berubah karena roda tetikus. --}}
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2 text-sm text-gray-500">
                            Rp
                        </span>
                        <input type="text" inputmode="numeric" autocomplete="off" required
                               :value="format(baris.nominal)"
                               x-on:input="ketikNominal(baris, $event)"
                               class="block w-full rounded-md border-gray-300 pl-8 text-sm shadow-sm {{ $gaya['fokus'] }}">
                    </div>

                    {{-- Nilai sebenarnya yang dibaca server: angka tanpa titik. --}}
                    <input type="hidden" :name="'penerima[' + i + '][nominal]'" :value="baris.nominal">
                </div>

            </div>
        </div>
    </template>

    {{-- Ringkasan berjalan agar Bendahara tahu sisa sebelum mengirim --}}
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm">
        <span class="text-gray-600">
            Total <span x-text="penerima.length"></span> penerima:
            <strong x-text="rupiah(total)"></strong>
        </span>
        <span :class="sisa < 0 ? 'text-red-600 font-semibold' : 'text-gray-600'">
            <template x-if="sisa >= 0">
                <span>Sisa: <strong x-text="rupiah(sisa)"></strong></span>
            </template>
            <template x-if="sisa < 0">
                <span>Melebihi batas <strong x-text="rupiah(-sisa)"></strong></span>
            </template>
        </span>
    </div>

    @error('penerima')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
