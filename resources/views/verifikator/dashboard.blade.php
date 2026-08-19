<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Pengajuan Masuk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border-b py-2">No. Pengajuan</th>
                            <th class="border-b py-2">Pengaju</th>
                            <th class="border-b py-2">Nominal</th>
                            <th class="border-b py-2">Status</th>
                            <th class="border-b py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan as $item)
                            <tr>
                                <td class="py-2 border-b">{{ $item->no_pengajuan }}</td>
                                <!-- Gunakan pemohon, bukan user -->
                                <td class="py-2 border-b">{{ $item->pemohon->name ?? '-' }}</td>
                                <td class="py-2 border-b">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                                <td class="py-2 border-b">
                                    <x-status-badge :kode="'MENUNGGU'" :label="'Menunggu Verifikasi ' . auth()->user()->urutan_verifikator" />
                                </td>
                                <td class="py-2 border-b">
                                    <a href="{{ route('verifikator.pengajuan.show', $item->id_pengajuan) }}"
                                        class="text-blue-600 hover:underline">Periksa</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-500">Tidak ada pengajuan baru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
```
