<x-app-layout title="Daftar Pengajuan">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <x-input-label for="status" value="Filter Status" />
                <select id="status" name="status" class="mt-1 rounded-lg border-ui-border bg-ui-card text-sm focus:border-pov-pengajuan focus:ring-pov-pengajuan">
                    <option value="">Semua status</option>
                    @foreach($statusList as $status)
                        <option value="{{ $status->kode_status }}" @selected(request('status') === $status->kode_status)>{{ $status->nama_status }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg border border-ui-border bg-ui-card px-4 py-2 text-sm font-medium">Terapkan</button>
        </form>
        <a href="{{ route('pegawai.pengajuan.create') }}" class="rounded-lg bg-pov-pengajuan px-4 py-2 text-sm font-semibold text-ui-card">+ Ajukan Pembayaran</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-4 py-3">No. Pengajuan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Perihal</th>
                        <th class="px-4 py-3">Nominal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @forelse($pengajuan as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $item->no_pengajuan }}</td>
                            <td class="px-4 py-3">{{ $item->tanggal_pengajuan->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $item->perihal }}</td>
                            <td class="px-4 py-3">Rp {{ number_format((float) $item->total_nominal, 2, ',', '.') }}</td>
                            <td class="px-4 py-3"><x-status-badge :kode="$item->status->kode_status" :label="$item->status->nama_status" /></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('pegawai.pengajuan.show', $item) }}" class="font-medium text-pov-pengajuan hover:underline">Detail</a>
                                @if($item->status->kode_status === 'REVISI')
                                    <span class="mx-1 text-ui-muted">·</span>
                                    <a href="{{ route('pegawai.pengajuan.edit', $item) }}" class="font-medium text-status-revisi hover:underline">Perbaiki</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-ui-muted">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $pengajuan->links() }}</div>
</x-app-layout>
