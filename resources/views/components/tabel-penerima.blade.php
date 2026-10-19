{{--
    Daftar penerima yang diusulkan pengaju, tampilan baca saja.

    Dipakai bersama oleh seluruh role agar daftar yang sama terbaca sama di mana
    pun: detail pengajuan pegawai, layar verifikator, PPK, PPSPM, Bendahara,
    riwayat, dan monitoring admin.

    Tidak merender apa pun bila pengajuan tidak punya daftar penerima — SPJ
    dengan satu penerima cukup diwakili kolom "Nama Penerima".

    - $penerima : koleksi App\Models\PenerimaPengajuan
    - $total    : total nominal pengajuan, untuk menunjukkan selisih (opsional)
    - $ringkas  : true untuk kotak tanpa judul besar, dipakai di kolom sempit
--}}
@props([
    'penerima',
    'total' => null,
    'ringkas' => false,
])

@php
    $daftar = collect($penerima);
    $totalDaftar = (float) $daftar->sum('nominal');
    $selisih = $total === null ? null : (float) $total - $totalDaftar;
@endphp

@if($daftar->isNotEmpty())
    <div class="rounded-lg border border-ui-border bg-ui-card">
        <div class="border-b border-ui-border px-4 py-3">
            <p class="{{ $ringkas ? 'text-sm' : 'text-base' }} font-semibold text-ui-text">
                Daftar Penerima dari Pengaju ({{ $daftar->count() }})
            </p>
            <p class="mt-1 text-xs text-ui-muted">
                Diisi pengaju saat membuat SPJ. Dipakai Bendahara sebagai dasar pencairan.
            </p>
        </div>

        <div class="max-h-80 overflow-y-auto">
            <table class="w-full text-left text-sm">
                <thead class="sticky top-0 bg-ui-page">
                    <tr class="text-xs font-semibold uppercase text-ui-muted">
                        <th class="w-12 px-4 py-2">No</th>
                        <th class="px-4 py-2">Nama Penerima</th>
                        <th class="px-4 py-2 text-right">Besaran Uang</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-ui-border">
                    @foreach($daftar as $baris)
                        <tr>
                            <td class="px-4 py-2 text-ui-muted">{{ $baris->urutan ?? $loop->iteration }}</td>
                            <td class="px-4 py-2 text-ui-text">{{ $baris->nama_penerima }}</td>
                            <td class="px-4 py-2 text-right font-medium text-ui-text">
                                Rp {{ number_format((float) $baris->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot class="border-t border-ui-border bg-ui-page">
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-xs font-semibold uppercase text-ui-muted">
                            Total Daftar
                        </td>
                        <td class="px-4 py-2 text-right font-bold text-ui-text">
                            Rp {{ number_format($totalDaftar, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Selisih ditampilkan apa adanya: ada SPJ yang memang hanya merinci
             sebagian penerimanya, jadi ini keterangan, bukan peringatan kesalahan. --}}
        @if($selisih !== null && abs($selisih) >= 1)
            <p class="border-t border-ui-border px-4 py-2 text-xs text-ui-muted">
                Selisih dengan total pengajuan:
                <strong class="text-ui-text">Rp {{ number_format(abs($selisih), 0, ',', '.') }}</strong>
                {{ $selisih > 0 ? 'belum dirinci dalam daftar' : 'daftar melebihi total pengajuan' }}.
            </p>
        @endif
    </div>
@endif
