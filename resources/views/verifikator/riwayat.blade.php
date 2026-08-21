<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Proses Bendahara') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border py-2 px-4">No. Pengajuan</th>
                            <th class="border py-2 px-4">Pemohon</th>
                            <th class="border py-2 px-4">Tahap Proses</th>
                            <th class="border py-2 px-4">Tanggal Diproses</th>
                            <th class="border py-2 px-4">Aksi / Status</th>
                            <th class="border py-2 px-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $item)
                        <tr>
                            <td class="border py-2 px-4">{{ $item->pengajuan->no_pengajuan ?? '-' }}</td>
                            <td class="border py-2 px-4">{{ $item->pengajuan->user->nama_lengkap ?? $item->pengajuan->user->name ?? '-' }}</td>
                            <td class="border py-2 px-4 font-semibold text-gray-700">Tahap {{ $item->tahap }}</td>
                            
                            <!-- Penyesuaian nama kolom tanggal untuk tabel verifikasi -->
                            <td class="border py-2 px-4">
                                {{ \Carbon\Carbon::parse($item->tanggal_verifikasi ?? $item->created_at)->format('d M Y H:i') }}
                            </td>
                            
                            <!-- Penyesuaian nama kolom status untuk tabel verifikasi -->
                            <td class="border py-2 px-4">
    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold">
        {{ $item->statusVerifikasi->nama_status ?? ($item->id_status_verifikasi == 1 ? 'ACC' : ($item->id_status_verifikasi == 2 ? 'REVISI' : 'DITOLAK')) }}
    </span>
</td>
                            
                            <td class="border py-2 px-4 text-sm">{{ $item->catatan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="border py-4 text-center text-gray-500">Belum ada riwayat proses.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $riwayat->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>