<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Keputusan Verifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border-b py-2">Tanggal</th>
                            <th class="border-b py-2">No. Pengajuan</th>
                            <th class="border-b py-2">Keputusan</th>
                            <th class="border-b py-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $item)
                        <tr>
                            <td class="py-2 border-b">{{ \Carbon\Carbon::parse($item->tanggal_verifikasi)->format('d M Y H:i') }}</td>
                            <td class="py-2 border-b">{{ $item->pengajuan->no_pengajuan ?? '-' }}</td>
                            <td class="py-2 border-b">
                                <!-- Asumsi relasi statusVerifikasi tersedia -->
                                <x-status-badge :kode="$item->statusVerifikasi->kode ?? 'NEUTRAL'" :label="$item->statusVerifikasi->nama ?? 'Unknown'" />
                            </td>
                            <td class="py-2 border-b">{{ $item->catatan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Belum ada riwayat verifikasi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>