{{--
    Daftar penerima yang diusulkan pengaju, diisi manual atau diimpor dari
    berkas Excel/CSV berformat: nomor, nama, besaran uang.

    Daftar ini opsional. SPJ dengan satu penerima cukup mengandalkan kolom
    "Nama Penerima" di atasnya. Bila diisi, daftarnya diteruskan ke Bendahara
    sebagai isian awal form pencairan sehingga Bendahara tinggal menyetujui.

    Berkas dibaca di peramban, bukan diunggah ke server: pengaju bisa memeriksa
    dan memperbaiki hasil pembacaan sebelum mengirim, dan server tidak perlu
    pustaka pembaca Excel. Yang terkirim ke server tetap baris-baris biasa.

    - $penerima : baris awal (dipakai saat merevisi pengajuan)
--}}
@props(['penerima' => []])

@php
    // old() menang atas data tersimpan supaya isian tidak hilang saat validasi gagal.
    $lama = old('penerima');

    $awal = is_array($lama)
        ? collect($lama)
            ->filter(fn ($baris) => is_array($baris))
            ->map(fn ($baris) => [
                'nama_penerima' => (string) ($baris['nama_penerima'] ?? ''),
                'nominal' => (string) ($baris['nominal'] ?? ''),
            ])
            ->values()
            ->all()
        : collect($penerima)
            ->map(fn ($baris) => [
                'nama_penerima' => (string) (is_array($baris) ? $baris['nama_penerima'] : $baris->nama_penerima),
                // Desimal dibuang: nominal rupiah di aplikasi ini selalu bulat.
                'nominal' => (string) (int) round((float) (is_array($baris) ? $baris['nominal'] : $baris->nominal)),
            ])
            ->values()
            ->all();
@endphp

<div x-data="daftarPenerimaPengajuan(@js($awal))" class="rounded-lg border border-ui-border bg-ui-card p-4">

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-ui-text">Daftar Penerima Pembayaran</p>
            <p class="mt-1 text-xs text-ui-muted">
                Opsional. Isi bila uang ini dibayarkan ke beberapa orang, agar Bendahara
                tidak perlu mengetik ulang daftarnya.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" x-on:click="tambah()"
                    class="rounded-lg border border-ui-border px-3 py-1.5 text-xs font-semibold text-pov-pengajuan hover:bg-ui-page">
                + Tambah Penerima
            </button>

            <label class="cursor-pointer rounded-lg border border-ui-border px-3 py-1.5 text-xs font-semibold text-pov-pengajuan hover:bg-ui-page">
                Impor Excel/CSV
                <input type="file" class="hidden" accept=".xlsx,.xls,.csv"
                       x-ref="berkas" x-on:change="impor($event)">
            </label>

            <button type="button" x-on:click="unduhContoh()"
                    class="rounded-lg border border-ui-border px-3 py-1.5 text-xs font-semibold text-ui-muted hover:bg-ui-page">
                Unduh Contoh
            </button>
        </div>
    </div>

    {{-- Hasil pembacaan berkas: berapa baris masuk, berapa dilewati, dan kenapa. --}}
    <template x-if="pesan">
        <p class="mt-3 rounded-lg px-3 py-2 text-xs"
           x-bind:class="gagal ? 'bg-status-rejected/10 text-status-rejected' : 'bg-status-done/10 text-status-done'"
           x-text="pesan"></p>
    </template>

    <template x-if="baris.length === 0">
        <p class="mt-3 rounded-lg border border-dashed border-ui-border px-3 py-6 text-center text-xs text-ui-muted">
            Belum ada daftar penerima. Tambahkan manual, atau impor dari berkas Excel
            berisi kolom nomor, nama, dan besaran uang.
        </p>
    </template>

    <template x-if="baris.length > 0">
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-ui-border text-xs font-semibold uppercase text-ui-muted">
                        <th class="w-12 py-2 pr-2">No</th>
                        <th class="py-2 pr-2">Nama Penerima</th>
                        <th class="w-56 py-2 pr-2">Besaran Uang</th>
                        <th class="w-16 py-2"></th>
                    </tr>
                </thead>

                <tbody>
                    <template x-for="(item, i) in baris" x-bind:key="i">
                        <tr class="border-b border-ui-border last:border-0">
                            <td class="py-2 pr-2 text-ui-muted" x-text="i + 1"></td>

                            <td class="py-2 pr-2">
                                <input type="text" x-model="item.nama_penerima"
                                       x-bind:name="'penerima[' + i + '][nama_penerima]'"
                                       maxlength="150" autocomplete="off"
                                       class="block w-full rounded-lg border-ui-border bg-ui-card text-sm focus:border-pov-pengajuan focus:ring-pov-pengajuan">
                            </td>

                            <td class="py-2 pr-2">
                                {{-- Kolom yang dilihat pengaju: teks berpemisah ribuan, tanpa
                                     tombol panah dan tidak bisa berubah karena roda tetikus. --}}
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-ui-muted">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off"
                                           x-bind:value="format(item.nominal)"
                                           x-on:input="ketikNominal(item, $event)"
                                           class="block w-full rounded-lg border-ui-border bg-ui-card pl-10 text-sm focus:border-pov-pengajuan focus:ring-pov-pengajuan">
                                </div>

                                {{-- Nilai sebenarnya yang dibaca server: angka tanpa titik. --}}
                                <input type="hidden" x-bind:name="'penerima[' + i + '][nominal]'" x-bind:value="item.nominal">
                            </td>

                            <td class="py-2">
                                <button type="button" x-on:click="hapus(i)"
                                        class="text-xs font-semibold text-status-rejected hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    {{-- Ringkasan berjalan: total daftar dibanding total nominal pengajuan,
         supaya selisihnya terlihat sebelum form dikirim. --}}
    <template x-if="baris.length > 0">
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-ui-page px-3 py-2 text-sm">
            <span class="text-ui-muted">
                <span x-text="baris.length"></span> penerima ·
                total <strong class="text-ui-text" x-text="rupiah(total)"></strong>
            </span>

            <span class="flex flex-wrap items-center gap-3">
                <template x-if="selisih !== 0">
                    <span x-bind:class="selisih > 0 ? 'font-semibold text-status-rejected' : 'text-status-pending'"
                          x-text="selisih > 0
                              ? 'Melebihi total pengajuan ' + rupiah(selisih)
                              : 'Kurang ' + rupiah(-selisih) + ' dari total pengajuan'"></span>
                </template>

                <template x-if="selisih === 0 && total > 0">
                    <span class="font-semibold text-status-done">Cocok dengan total pengajuan</span>
                </template>

                <button type="button" x-on:click="samakanTotal()" x-show="selisih !== 0 && total > 0"
                        class="rounded-lg border border-ui-border px-2.5 py-1 text-xs font-semibold text-pov-pengajuan hover:bg-ui-card">
                    Pakai total daftar
                </button>

                <button type="button" x-on:click="kosongkan()"
                        class="text-xs font-semibold text-ui-muted hover:underline">
                    Kosongkan
                </button>
            </span>
        </div>
    </template>

    @error('penerima')<p class="mt-2 text-sm text-status-rejected">{{ $message }}</p>@enderror
    @foreach($errors->get('penerima.*') as $pesanBaris)
        <p class="mt-1 text-sm text-status-rejected">{{ $pesanBaris[0] }}</p>
    @endforeach
</div>

@once
    @push('skrip')
        <script>
            /**
             * Daftar penerima pengajuan.
             *
             * Berkas Excel dibaca di peramban dengan SheetJS yang dimuat sesuai
             * kebutuhan — hanya saat pengaju benar-benar mengimpor berkas, bukan
             * pada setiap kunjungan halaman. Bila pustaka itu gagal dimuat (mis.
             * tidak ada akses internet), berkas CSV tetap bisa dibaca dengan
             * pembaca sederhana di bawah, dan pengisian manual tidak terpengaruh.
             */
            document.addEventListener('alpine:init', () => {
                const SUMBER_SHEETJS = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';

                Alpine.data('daftarPenerimaPengajuan', (awal) => ({
                    baris: awal,
                    pesan: '',
                    gagal: false,
                    totalPengajuan: 0,

                    init() {
                        this.bacaTotalPengajuan();

                        // Total nominal pengajuan diisi di kolom lain pada form yang
                        // sama. Perubahannya diikuti supaya selisih yang ditampilkan
                        // tidak basi.
                        const induk = this.$el.closest('form') ?? document;
                        induk.addEventListener('input', () => this.bacaTotalPengajuan());
                    },

                    bacaTotalPengajuan() {
                        const kolom = (this.$el.closest('form') ?? document)
                            .querySelector('input[name="total_nominal"]');

                        this.totalPengajuan = kolom ? (parseFloat(kolom.value) || 0) : 0;
                    },

                    get total() {
                        return this.baris.reduce((jumlah, item) => jumlah + (parseFloat(item.nominal) || 0), 0);
                    },

                    /* Positif berarti daftar melebihi total pengajuan. */
                    get selisih() {
                        return this.total - this.totalPengajuan;
                    },

                    tambah() {
                        this.baris.push({ nama_penerima: '', nominal: '' });
                        this.pesan = '';
                    },

                    hapus(i) {
                        this.baris.splice(i, 1);
                    },

                    kosongkan() {
                        if (this.baris.length > 0 && ! confirm('Kosongkan seluruh daftar penerima?')) {
                            return;
                        }

                        this.baris = [];
                        this.pesan = '';
                    },

                    /* Isi kolom Total Nominal dengan total daftar penerima. */
                    samakanTotal() {
                        const form = this.$el.closest('form') ?? document;
                        const tampil = form.querySelector('[data-rupiah-tampil="total_nominal"]');
                        const nilai = form.querySelector('input[name="total_nominal"]');

                        if (! nilai) {
                            return;
                        }

                        nilai.value = String(Math.round(this.total));

                        // Kolom yang terlihat diperbarui lewat Alpine bila ada, agar
                        // angka berpemisah ribuannya ikut ganti.
                        if (tampil) {
                            tampil.value = this.format(nilai.value);
                            tampil.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        this.bacaTotalPengajuan();
                    },

                    rupiah(angka) {
                        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
                    },

                    /* Sisakan digit saja, lalu buang nol di depan supaya '007' jadi '7'. */
                    bersihkan(teks) {
                        return String(teks ?? '').replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                    },

                    format(angka) {
                        return angka === '' || angka === null || angka === undefined
                            ? ''
                            : Number(angka).toLocaleString('id-ID');
                    },

                    ketikNominal(item, peristiwa) {
                        item.nominal = this.bersihkan(peristiwa.target.value);
                        peristiwa.target.value = this.format(item.nominal);
                    },

                    async impor(peristiwa) {
                        const berkas = peristiwa.target.files[0];

                        if (! berkas) {
                            return;
                        }

                        this.pesan = 'Membaca berkas…';
                        this.gagal = false;

                        try {
                            const tabel = await this.bacaBerkas(berkas);
                            this.terapkan(tabel);
                        } catch (galat) {
                            this.gagal = true;
                            this.pesan = 'Berkas tidak dapat dibaca: ' + (galat.message ?? galat)
                                + '. Daftar bisa diisi manual.';
                        } finally {
                            // Supaya berkas yang sama bisa dipilih ulang setelah diperbaiki.
                            this.$refs.berkas.value = '';
                        }
                    },

                    /**
                     * Kembalikan isi berkas sebagai array baris berisi array sel.
                     */
                    async bacaBerkas(berkas) {
                        const csv = /\.csv$/i.test(berkas.name);

                        try {
                            await this.muatSheetJs();
                        } catch (galat) {
                            if (! csv) {
                                throw new Error('pembaca Excel gagal dimuat, perlu koneksi internet. Berkas CSV tetap bisa diimpor');
                            }

                            return this.bacaCsv(await berkas.text());
                        }

                        const data = new Uint8Array(await berkas.arrayBuffer());
                        const buku = XLSX.read(data, { type: 'array' });
                        const lembar = buku.Sheets[buku.SheetNames[0]];

                        if (! lembar) {
                            throw new Error('berkas tidak memuat lembar kerja');
                        }

                        return XLSX.utils.sheet_to_json(lembar, { header: 1, blankrows: false, raw: true });
                    },

                    muatSheetJs() {
                        if (window.XLSX) {
                            return Promise.resolve();
                        }

                        if (! this._pustaka) {
                            this._pustaka = new Promise((selesai, tolak) => {
                                const skrip = document.createElement('script');
                                skrip.src = SUMBER_SHEETJS;
                                skrip.onload = () => selesai();
                                skrip.onerror = () => {
                                    // Promise yang gagal tidak di-cache, supaya percobaan
                                    // berikutnya memuat ulang pustakanya.
                                    this._pustaka = null;
                                    tolak(new Error('gagal memuat pembaca Excel'));
                                };
                                document.head.appendChild(skrip);
                            });
                        }

                        return this._pustaka;
                    },

                    /**
                     * Pembaca CSV sederhana: pemisah koma atau titik koma, dengan
                     * dukungan tanda kutip ganda untuk sel yang memuat pemisah.
                     */
                    bacaCsv(teks) {
                        const pemisah = (teks.split('\n')[0].match(/;/g) ?? []).length
                            > (teks.split('\n')[0].match(/,/g) ?? []).length ? ';' : ',';

                        return teks
                            .replace(/^﻿/, '')
                            .split(/\r?\n/)
                            .filter((garis) => garis.trim() !== '')
                            .map((garis) => {
                                const sel = [];
                                let kini = '';
                                let dalamKutip = false;

                                for (let i = 0; i < garis.length; i++) {
                                    const huruf = garis[i];

                                    if (huruf === '"') {
                                        if (dalamKutip && garis[i + 1] === '"') {
                                            kini += '"';
                                            i++;
                                        } else {
                                            dalamKutip = ! dalamKutip;
                                        }
                                    } else if (huruf === pemisah && ! dalamKutip) {
                                        sel.push(kini);
                                        kini = '';
                                    } else {
                                        kini += huruf;
                                    }
                                }

                                sel.push(kini);

                                return sel;
                            });
                    },

                    /**
                     * Ubah tabel mentah menjadi baris penerima.
                     *
                     * Kolom dikenali dari baris judul bila ada (mis. "Nama", "Nominal"),
                     * karena berkas dari pengguna jarang persis sama susunannya. Tanpa
                     * baris judul, susunan dianggap nomor-nama-nominal, dan bila hanya
                     * dua kolom dianggap nama-nominal.
                     */
                    terapkan(tabel) {
                        if (! Array.isArray(tabel) || tabel.length === 0) {
                            throw new Error('berkas kosong');
                        }

                        let kolomNama = -1;
                        let kolomNominal = -1;
                        let mulai = 0;

                        // Baris judul dicari di beberapa baris pertama: berkas sering
                        // diawali judul laporan atau baris kosong.
                        for (let i = 0; i < Math.min(tabel.length, 5); i++) {
                            const sel = (tabel[i] ?? []).map((nilai) => String(nilai ?? '').toLowerCase().trim());

                            const iNama = sel.findIndex((nilai) => nilai.includes('nama'));
                            const iNominal = sel.findIndex((nilai) =>
                                /nominal|besaran|jumlah|uang|rupiah|nilai/.test(nilai));

                            if (iNama !== -1 && iNominal !== -1) {
                                kolomNama = iNama;
                                kolomNominal = iNominal;
                                mulai = i + 1;
                                break;
                            }
                        }

                        if (kolomNama === -1) {
                            const lebar = Math.max(...tabel.map((garis) => (garis ?? []).length));

                            kolomNama = lebar >= 3 ? 1 : 0;
                            kolomNominal = lebar >= 3 ? 2 : 1;
                        }

                        const hasil = [];
                        let dilewati = 0;

                        for (let i = mulai; i < tabel.length; i++) {
                            const garis = tabel[i] ?? [];
                            const nama = String(garis[kolomNama] ?? '').trim();
                            const nominal = this.angkaDariSel(garis[kolomNominal]);

                            if (nama === '' && nominal === '') {
                                continue;
                            }

                            if (nama === '' || nominal === '' || Number(nominal) <= 0) {
                                dilewati++;
                                continue;
                            }

                            hasil.push({
                                nama_penerima: nama.slice(0, 150),
                                nominal: nominal,
                            });
                        }

                        if (hasil.length === 0) {
                            throw new Error('tidak ada baris berisi nama dan besaran uang yang bisa dibaca');
                        }

                        this.baris = hasil;
                        this.gagal = false;
                        this.pesan = hasil.length + ' penerima berhasil dibaca'
                            + (dilewati > 0 ? ', ' + dilewati + ' baris dilewati karena nama atau nominalnya kosong' : '')
                            + '. Periksa dan perbaiki bila perlu sebelum mengirim.';
                    },

                    /**
                     * Sel nominal bisa berupa angka asli dari Excel, atau teks seperti
                     * "Rp 1.500.000". Keduanya diubah menjadi digit saja.
                     */
                    angkaDariSel(nilai) {
                        if (typeof nilai === 'number') {
                            return String(Math.round(nilai));
                        }

                        return this.bersihkan(nilai);
                    },

                    /**
                     * Berkas contoh dibuat di peramban agar tidak perlu berkas statis
                     * yang bisa tertinggal versinya. CSV dipilih karena bisa dibuka
                     * dan disimpan ulang Excel sebagai .xlsx.
                     */
                    unduhContoh() {
                        const isi = 'Nomor,Nama,Nominal\n'
                            + '1,Budi Santoso,1500000\n'
                            + '2,Siti Aminah,1250000\n'
                            + '3,Agus Pranoto,900000\n';

                        // BOM UTF-8 supaya Excel membaca huruf beraksen dengan benar.
                        const berkas = new Blob(['﻿' + isi], { type: 'text/csv;charset=utf-8' });
                        const tautan = document.createElement('a');

                        tautan.href = URL.createObjectURL(berkas);
                        tautan.download = 'contoh-daftar-penerima.csv';
                        tautan.click();

                        URL.revokeObjectURL(tautan.href);
                    },
                }));
            });
        </script>
    @endpush
@endonce
