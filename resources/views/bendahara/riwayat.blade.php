<x-app-layout title="Riwayat Proses Bendahara">

    <div class="mb-5">
        <h2 class="text-2xl font-bold">Riwayat Proses Bendahara</h2>
        <p class="mt-1 text-sm text-ui-muted">Seluruh pengajuan yang pernah Anda proses. Klik Detail untuk melihat riwayat lengkap dari verifikasi hingga pencairan.</p>
    </div>

    <div class="rounded-xl border border-ui-border bg-ui-card p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border py-2 px-4">No. Pengajuan</th>
                        <th class="border py-2 px-4">Pemohon</th>
                        <th class="border py-2 px-4">Tahap Proses</th>
                        <th class="border py-2 px-4">Dokumen</th>
                        <th class="border py-2 px-4">Tanggal Diproses</th>
                        <th class="border py-2 px-4">Aksi / Status</th>
                        <th class="border py-2 px-4">Catatan</th>
                        <th class="border py-2 px-4">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $item)
                        <tr>
                            <td class="border py-2 px-4">{{ $item->pengajuan->no_pengajuan ?? '-' }}</td>
                            <td class="border py-2 px-4">{{ $item->pengajuan->user->nama_lengkap ?? $item->pengajuan->user->name ?? '-' }}</td>
                            <td class="border py-2 px-4 font-semibold text-gray-700">{{ $item->tahap ? str_replace('_', ' ', $item->tahap) : '-' }}</td>
                            <td class="border py-2 px-4 text-sm">
                                @if($item->tahap === 'PENGAJUAN_SPP')
                                    SPP: {{ $item->no_spp ?? '-' }}
                                    @if($item->tgl_spp)
                                        <span class="text-gray-500">({{ \Carbon\Carbon::parse($item->tgl_spp)->format('d M Y') }})</span>
                                    @endif
                                @elseif(in_array($item->tahap, ['KONFIRMASI', 'PEMBAYARAN_LANGSUNG']))
                                    @if($item->cara_bayar === 'TUNAI')
                                        Kwitansi: {{ $item->no_kwitansi ?? '-' }}
                                        @if($item->tgl_transfer)
                                            <span class="text-gray-500">(tunai {{ \Carbon\Carbon::parse($item->tgl_transfer)->format('d M Y') }})</span>
                                        @endif
                                    @else
                                        SP2D: {{ $item->no_sp2d ?? '-' }}
                                        @if($item->tgl_transfer)
                                            <span class="text-gray-500">(transfer {{ \Carbon\Carbon::parse($item->tgl_transfer)->format('d M Y') }})</span>
                                        @endif
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="border py-2 px-4">{{ $item->tanggal_proses ? \Carbon\Carbon::parse($item->tanggal_proses)->format('d M Y H:i') : '-' }}</td>
                            <td class="border py-2 px-4">
                                @if($item->id_status == 1)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">ACC</span>
                                @elseif($item->id_status == 2)
                                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-bold">REVISI</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">DITOLAK</span>
                                @endif
                            </td>
                            <td class="border py-2 px-4 text-sm">{{ $item->catatan ?? '-' }}</td>
                            <td class="border py-2 px-4">
                                @if($item->pengajuan)
                                    <a href="{{ route('riwayat.detail', $item->pengajuan->id_pengajuan) }}"
                                       class="inline-flex rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700">
                                        Detail
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="border py-4 text-center text-gray-500">Belum ada riwayat proses.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $riwayat->links() }}
        </div>
    </div>
</x-app-layout>
