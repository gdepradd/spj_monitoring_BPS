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
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-ui-border text-xs font-semibold uppercase text-ui-muted">
                <th class="px-6 py-4">No. Pengajuan</th>
                <th class="px-6 py-4">Tanggal Pengajuan</th>
                <th class="px-6 py-4">Tanggal Selesai</th>
                <th class="px-6 py-4">Nominal</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-ui-border text-sm">
            @forelse($pengajuan as $item)
            <tr class="hover:bg-ui-page/50 transition">
                <td class="px-6 py-5 font-medium text-ui-text">
                    {{ $item->no_pengajuan }}
                </td>
                <td class="px-6 py-5 text-ui-text">
                    {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d/m/Y') }}
                </td>
                <td class="px-6 py-5 text-ui-text">
                    @if($item->tanggal_selesai)
                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                    @else
                        <span class="text-ui-muted">—</span>
                    @endif
                </td>
                <td class="px-6 py-5 font-medium text-ui-text">
                    Rp {{ number_format((float)$item->total_nominal, 0, ',', '.') }}
                </td>
                <td class="px-6 py-5">
                    <span
                        @class([
                            'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-amber-100 text-amber-700' => in_array($item->status?->kode_status, [
                                'DIAJUKAN', 'VERIFIKASI_1', 'VERIFIKASI_2', 'VERIFIKASI_3', 
                                'MENUNGGU_PENCAIRAN', 'PROSES_PPK', 'PROSES_PPSPM', 'PROSES_KONFIRMASI_BENDAHARA'
                            ]),
                            'bg-orange-100 text-orange-700' => $item->status?->kode_status === 'REVISI',
                            'bg-red-100 text-red-700' => $item->status?->kode_status === 'DITOLAK',
                            'bg-green-100 text-green-700' => $item->status?->kode_status === 'SELESAI',
                        ])
                    >
                        {{ $item->status?->nama_status ?? '-' }}
                    </span>
                </td>
                <td class="px-6 py-5">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('pegawai.pengajuan.show', $item) }}" class="font-medium text-blue-600 hover:underline">
                            Detail
                        </a>
                        @if($item->status?->kode_status === 'REVISI')
                            <a href="{{ route('pegawai.pengajuan.edit', $item) }}" class="font-medium text-orange-600 hover:underline">
                                Perbaiki
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-sm text-ui-muted">
                    Tidak ada pengajuan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
    </div>

    <div class="mt-5">{{ $pengajuan->links() }}</div>
</x-app-layout>
