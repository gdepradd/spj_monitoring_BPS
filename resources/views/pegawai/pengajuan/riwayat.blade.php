<x-app-layout title="Riwayat Pengajuan">
    <div class="mb-5">
        <h2 class="text-2xl font-bold">Riwayat Pengajuan</h2>
        <p class="mt-1 text-sm text-ui-muted">Menampilkan seluruh pengajuan, termasuk yang selesai dan ditolak.</p>
    </div>

    <div class="space-y-3">
        @forelse($pengajuan as $item)
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-ui-border bg-ui-card p-4">
                <a href="{{ route('pegawai.pengajuan.show', $item) }}" class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $item->no_pengajuan }} — {{ $item->perihal }}</p>
                    <p class="mt-1 text-sm text-ui-muted">{{ $item->tanggal_pengajuan->format('d M Y') }} · Rp {{ number_format((float) $item->total_nominal, 2, ',', '.') }}</p>
                </a>

                <div class="flex items-center gap-3">
                    <x-status-badge :kode="$item->status->kode_status" :label="$item->status->nama_status" />

                    <a href="{{ route('riwayat.detail', $item->id_pengajuan) }}"
                       class="inline-flex rounded-lg border border-ui-border px-3 py-1 text-xs font-semibold hover:bg-ui-page">
                        Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-ui-border bg-ui-card p-10 text-center text-ui-muted">Belum ada riwayat.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $pengajuan->links() }}</div>
</x-app-layout>
