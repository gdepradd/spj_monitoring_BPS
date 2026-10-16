{{--
    Input nominal rupiah.

    Dipakai menggantikan <input type="number"> untuk kolom uang karena:
    - type="number" memunculkan tombol panah di kanan dan nilainya bisa
      berubah hanya karena roda tetikus digulir di atas kolom yang fokus;
    - type="number" tidak bisa menampilkan pemisah ribuan, sehingga angka
      panjang seperti 200000 sukar dibaca.

    Yang terlihat pengguna adalah kolom teks berformat "200.000". Nilai yang
    dikirim ke server tetap angka murni lewat input hidden, jadi aturan
    validasi 'numeric' di FormRequest tidak perlu diubah.

    - $name  : nama field yang diterima server
    - $value : nilai awal (boleh "10000000.00" dari kolom decimal)
--}}
@props([
    'name',
    'id' => null,
    'value' => null,
    'required' => false,
])

@php
    $idInput = $id ?? $name;

    // Rupiah di aplikasi ini selalu bulat, jadi desimal pada nilai awal
    // (mis. "10000000.00" dari kolom decimal) dibuang lebih dulu.
    $angkaAwal = ($value === null || $value === '')
        ? ''
        : (string) (int) round((float) $value);

    $tampilAwal = $angkaAwal === ''
        ? ''
        : number_format((int) $angkaAwal, 0, ',', '.');
@endphp

<div
    x-data="{
        angka: @js($angkaAwal),
        tampil: @js($tampilAwal),

        /* Sisakan digit saja, lalu buang nol di depan supaya '007' jadi '7'. */
        bersihkan(teks) {
            return String(teks).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        },

        format(angka) {
            return angka === '' ? '' : Number(angka).toLocaleString('id-ID');
        },

        ubah(peristiwa) {
            this.angka = this.bersihkan(peristiwa.target.value);
            this.tampil = this.format(this.angka);
        },
    }"
    class="mt-1"
>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-ui-muted">
            Rp
        </span>

        <input
            type="text"
            inputmode="numeric"
            autocomplete="off"
            id="{{ $idInput }}"
            x-model="tampil"
            x-on:input="ubah($event)"
            @required($required)
            {{ $attributes->merge([
                'class' => 'block w-full rounded-lg border-ui-border bg-ui-card pl-10 text-ui-text shadow-sm focus:border-pov-pengajuan focus:ring-pov-pengajuan',
            ]) }}
        >
    </div>

    {{-- Nilai sebenarnya yang dibaca server: angka tanpa titik. --}}
    <input type="hidden" name="{{ $name }}" x-bind:value="angka">
</div>
