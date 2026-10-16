<x-app-layout title="Riwayat Proses Bendahara">

    @php
        $rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');

        $tahapLabel = [
            'PENGAJUAN_SPP' => 'Pengajuan SPP',
            'PEMBAYARAN_LANGSUNG' => 'Pembayaran Langsung',
            'KONFIRMASI' => 'Konfirmasi Pencairan',
        ];
    @endphp

    <div class="mb-5">
        <h2 class="text-2xl font-bold">Riwayat Proses Bendahara</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Satu baris per nomor pengajuan. Klik Detail untuk melihat seluruh tahapan dari verifikasi hingga pencairan.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-4 py-3">No. Pengajuan</th>
                        <th class="px-4 py-3">Pemohon</th>
                        <th class="px-4 py-3">No. SPP</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Dibayar</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3">Proses Terakhir</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @forelse($riwayat as $item)
                        @php
                            // Catatan proses milik Bendahara yang sedang login, terbaru lebih dulu.
                            $prosesSaya = $item->bendahara
                                ->where('id_user', auth()->id())
                                ->sortByDesc('id_bendahara');

                            $terakhir = $prosesSaya->first();

                            $jumlahPembayaran = $item->bendahara
                                ->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
                                ->count();
                        @endphp
                        <tr class="hover:bg-ui-page">
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->no_pengajuan }}</p>
                                <p class="mt-1 text-xs text-ui-muted">
                                    {{ $prosesSaya->count() }} kali diproses
                                    @if($jumlahPembayaran > 1)
                                        · {{ $jumlahPembayaran }} termin bayar
                                    @endif
                                </p>
                            </td>
                            <td class="px-4 py-3">{{ $item->user->nama_lengkap ?? $item->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $item->no_spp ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $item->metode_label }}</td>
                            <td class="px-4 py-3 text-right">{{ $rupiah($item->total_nominal) }}</td>
                            <td class="px-4 py-3 text-right text-status-done">{{ $rupiah($item->total_dibayar) }}</td>
                            <td class="px-4 py-3 text-right {{ $item->sisa_pembayaran > 0 ? 'font-semibold text-status-rejected' : 'text-ui-muted' }}">
                                {{ $rupiah($item->sisa_pembayaran) }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($terakhir)
                                    <span class="font-medium text-ui-text">
                                        {{ $tahapLabel[$terakhir->tahap] ?? str_replace('_', ' ', $terakhir->tahap) }}
                                    </span>
                                    <span class="block text-ui-muted">
                                        {{ $terakhir->tanggal_proses ? \Carbon\Carbon::parse($terakhir->tanggal_proses)->format('d M Y H:i') : '-' }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :kode="$item->status?->kode_status" :label="$item->status?->nama_status" />
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('riwayat.detail', $item->id_pengajuan) }}"
                                   class="inline-flex rounded-lg bg-pov-pencairan px-3 py-1 text-xs font-semibold text-white hover:opacity-90">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-ui-muted">Belum ada riwayat proses.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $riwayat->links() }}</div>

</x-app-layout>
