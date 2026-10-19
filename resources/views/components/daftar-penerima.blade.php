{{--
    Komponen Daftar Penerima Pembayaran di form pengajuan.

    Fitur:
    - Tambah baris penerima secara manual
    - Impor dari file Excel/CSV (diproses di browser dengan SheetJS)
    - Unduh template CSV contoh

    Props:
    - $awalPenerima : array data penerima awal (untuk mode edit, default [])
--}}
@props(['awalPenerima' => []])

@php
    // Prioritas: old() saat validasi gagal, lalu $awalPenerima (edit), lalu kosong
    $lama = old('daftar_penerima');
    $dataAwal = is_array($lama) && $lama !== []
        ? array_values($lama)
        : (count($awalPenerima) > 0 ? $awalPenerima : []);
@endphp

<div
    x-data="{
        daftar: @js($dataAwal),
        fileError: '',

        tambah() {
            this.daftar.push({ nama: '', nominal: '' });
        },

        hapus(i) {
            this.daftar.splice(i, 1);
        },

        bersihkan(teks) {
            return String(teks).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        },

        format(angka) {
            return angka === '' || angka === null ? '' : Number(angka).toLocaleString('id-ID');
        },

        ketikNominal(baris, e) {
            baris.nominal = this.bersihkan(e.target.value);
            e.target.value = this.format(baris.nominal);
        },

        rupiah(angka) {
            return 'Rp\u00a0' + Math.round(angka || 0).toLocaleString('id-ID');
        },

        get totalNominal() {
            return this.daftar.reduce((sum, b) => sum + (parseFloat(b.nominal) || 0), 0);
        },

        imporFile(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.fileError = '';

            const reader = new FileReader();
            reader.onload = (ev) => {
                try {
                    const wb = XLSX.read(ev.target.result, { type: 'binary' });
                    const ws = wb.Sheets[wb.SheetNames[0]];
                    const rows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

                    // Cari baris header (baris yang ada kata 'nama')
                    let headerIdx = 0;
                    for (let i = 0; i < Math.min(rows.length, 5); i++) {
                        if (rows[i].some(c => String(c).toLowerCase().includes('nama'))) {
                            headerIdx = i;
                            break;
                        }
                    }

                    const header = rows[headerIdx].map(c => String(c).toLowerCase().trim());
                    const idxNama = header.findIndex(h => h.includes('nama'));
                    const idxNominal = header.findIndex(h => h.includes('nominal') || h.includes('jumlah') || h.includes('uang'));

                    if (idxNama === -1) {
                        this.fileError = 'Kolom \"nama\" tidak ditemukan. Pastikan file memiliki kolom nama.';
                        e.target.value = '';
                        return;
                    }

                    const baru = [];
                    for (let i = headerIdx + 1; i < rows.length; i++) {
                        const row = rows[i];
                        const nama = String(row[idxNama] ?? '').trim();
                        if (!nama) continue;
                        const nominalRaw = idxNominal >= 0 ? String(row[idxNominal] ?? '').replace(/\D/g, '') : '';
                        baru.push({ nama: nama, nominal: nominalRaw });
                    }

                    if (baru.length === 0) {
                        this.fileError = 'Tidak ada data penerima yang ditemukan dalam file.';
                    } else {
                        this.daftar = baru;
                    }
                } catch (err) {
                    this.fileError = 'Gagal membaca file. Pastikan format file Excel atau CSV yang benar.';
                }
                e.target.value = '';
            };
            reader.readAsBinaryString(file);
        },

        unduhContoh() {
            const bom = '\uFEFF';
            const csv = bom + 'No,Nama Penerima,Nominal\n1,Budi Santoso,500000\n2,Siti Aminah,750000\n3,Ahmad Fauzi,250000';
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'contoh_daftar_penerima.csv';
            a.click();
            URL.revokeObjectURL(url);
        }
    }"
    class="rounded-xl border border-ui-border bg-ui-card p-4"
>
    {{-- Header --}}
    <div class="mb-1">
        <p class="text-sm font-semibold text-ui-text">Daftar Penerima Pembayaran</p>
        <p class="text-xs text-ui-muted">Opsional. Isi bila uang ini dibayarkan ke beberapa orang, agar Bendahara tidak perlu mengetik ulang daftarnya.</p>
    </div>

    {{-- Toolbar --}}
    <div class="mt-3 flex flex-wrap gap-2">
        <button type="button" @click="tambah()"
            class="rounded-lg border border-ui-border px-3 py-1.5 text-xs font-semibold text-ui-text hover:bg-ui-page transition-colors">
            + Tambah Penerima
        </button>

        <label class="cursor-pointer rounded-lg border border-ui-border px-3 py-1.5 text-xs font-semibold text-ui-text hover:bg-ui-page transition-colors">
            Impor Excel/CSV
            <input type="file" accept=".xlsx,.xls,.csv" class="sr-only" @change="imporFile($event)">
        </label>

        <button type="button" @click="unduhContoh()"
            class="rounded-lg border border-ui-border px-3 py-1.5 text-xs font-semibold text-ui-text hover:bg-ui-page transition-colors">
            Unduh Contoh
        </button>
    </div>

    {{-- Error impor --}}
    <p x-show="fileError" x-text="fileError" class="mt-2 text-xs text-status-rejected"></p>

    {{-- Tabel penerima --}}
    <div class="mt-3">
        {{-- Placeholder kosong --}}
        <template x-if="daftar.length === 0">
            <div class="rounded-lg border border-dashed border-ui-border bg-ui-page px-4 py-5 text-center text-xs text-ui-muted">
                Belum ada daftar penerima. Tambahkan manual, atau impor dari berkas Excel berisi kolom <strong>nomor</strong>, <strong>nama</strong>, dan <strong>besaran uang</strong>.
            </div>
        </template>

        {{-- Baris penerima --}}
        <template x-if="daftar.length > 0">
            <div class="overflow-x-auto rounded-lg border border-ui-border">
                <table class="min-w-full divide-y divide-ui-border text-xs">
                    <thead class="bg-ui-page text-ui-muted">
                        <tr>
                            <th class="w-8 px-3 py-2 text-center">#</th>
                            <th class="px-3 py-2 text-left">Nama Penerima</th>
                            <th class="px-3 py-2 text-left">Nominal (Rp)</th>
                            <th class="w-12 px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ui-border">
                        <template x-for="(baris, i) in daftar" :key="i">
                            <tr>
                                <td class="px-3 py-2 text-center text-ui-muted" x-text="i + 1"></td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        x-model="baris.nama"
                                        :name="'daftar_penerima[' + i + '][nama]'"
                                        placeholder="Nama lengkap penerima"
                                        class="block w-full rounded-md border-ui-border bg-ui-card text-xs focus:border-pov-pengajuan focus:ring-pov-pengajuan"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2 text-xs text-ui-muted">Rp</span>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            autocomplete="off"
                                            :value="format(baris.nominal)"
                                            x-on:input="ketikNominal(baris, $event)"
                                            placeholder="0"
                                            class="block w-full rounded-md border-ui-border bg-ui-card pl-7 text-xs focus:border-pov-pengajuan focus:ring-pov-pengajuan"
                                        >
                                        <input type="hidden" :name="'daftar_penerima[' + i + '][nominal]'" :value="baris.nominal">
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="hapus(i)"
                                        class="text-status-rejected hover:underline text-xs font-medium">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-ui-page">
                        <tr>
                            <td colspan="2" class="px-3 py-2 text-right text-xs font-semibold text-ui-muted">Total:</td>
                            <td class="px-3 py-2 text-xs font-bold text-ui-text" x-text="rupiah(totalNominal)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </template>
    </div>
</div>

{{-- SheetJS untuk parsing Excel/CSV di browser --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
