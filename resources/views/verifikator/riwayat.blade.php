<x-app-layout title="Riwayat Verifikasi">

    <div class="mb-5">
        <h2 class="text-2xl font-bold">Riwayat Verifikasi</h2>
        <p class="mt-1 text-sm text-ui-muted">Seluruh pengajuan yang pernah Anda verifikasi. Klik Detail untuk melihat riwayat lengkap dari verifikasi hingga pencairan.</p>
    </div>

    <div class="rounded-xl border border-ui-border bg-ui-card p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border py-2 px-4">No. Pengajuan</th>
                        <th class="border py-2 px-4">Pemohon</th>
                        <th class="border py-2 px-4">Tanggal Diproses</th>
                        <th class="border py-2 px-4">Aksi / Status</th>
                        <th class="border py-2 px-4">Status Pengajuan Saat Ini</th>
                        <th class="border py-2 px-4">Catatan</th>
                        <th class="border py-2 px-4">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $item)
                        <tr>
                            <td class="border py-2 px-4">{{ $item->pengajuan->no_pengajuan ?? '-' }}</td>
                            <td class="border py-2 px-4">{{ $item->pengajuan->user->nama_lengkap ?? $item->pengajuan->user->name ?? '-' }}</td>
                            <td class="border py-2 px-4">
                                {{ \Carbon\Carbon::parse($item->tanggal_verifikasi ?? $item->created_at)->format('d M Y H:i') }}
                            </td>
                            <td class="border py-2 px-4">
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold">
                                    {{ $item->statusVerifikasi->nama_status ?? ($item->id_status_verifikasi == 2 ? 'ACC' : ($item->id_status_verifikasi == 3 ? 'REVISI' : 'DITOLAK')) }}
                                </span>
                            </td>
                            <td class="border py-2 px-4 text-sm">{{ $item->pengajuan->status->nama_status ?? '-' }}</td>
                            <td class="border py-2 px-4 text-sm">{{ $item->catatan ?? '-' }}</td>
                            <td class="border py-2 px-4">
                                @if($item->pengajuan)
                                    <a href="{{ route('riwayat.detail', $item->pengajuan->id_pengajuan) }}"
                                       class="inline-flex rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">
                                        Detail
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border py-4 text-center text-gray-500">Belum ada riwayat proses.</td>
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
