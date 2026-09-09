<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Pengajuan - Bendahara') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- SECTION 1: PERLU DIAJUKAN (PILIH METODE) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                <h3 class="font-bold text-lg mb-4">1. Menunggu Diajukan (Pilih Metode)</h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border-b py-2">No. Pengajuan</th>
                            <th class="border-b py-2">Pengaju</th>
                            <th class="border-b py-2">Nominal</th>
                            <th class="border-b py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perluDiajukan as $item)
                        <tr>
                            <td class="py-2 border-b">{{ $item->no_pengajuan }}</td>
                            <td class="py-2 border-b">{{ $item->user->nama_lengkap ?? $item->user->name ?? '-' }}</td>
                            <td class="py-2 border-b">Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
                            <td class="py-2 border-b">
                                <a href="{{ route('bendahara.pengajuan.show', $item->id_pengajuan) }}" class="text-blue-600 hover:underline">Proses</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Tidak ada pengajuan tahap awal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- SECTION 2: PERLU DIKONFIRMASI (TAHAP AKHIR) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                <h3 class="font-bold text-lg mb-4">2. Menunggu Konfirmasi Akhir (Dari Kemenkeu)</h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border-b py-2">No. Pengajuan</th>
                            <th class="border-b py-2">Pengaju</th>
                            <th class="border-b py-2">Metode</th>
                            <th class="border-b py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perluDikonfirmasi as $item)
                        <tr>
                            <td class="py-2 border-b">{{ $item->no_pengajuan }}</td>
                            <td class="py-2 border-b">{{ $item->user->nama_lengkap ?? $item->user->name ?? '-' }}</td>
                            <td class="py-2 border-b"><span class="bg-gray-200 px-2 py-1 rounded text-sm">{{ $item->metode_pembayaran }}</span></td>
                            <td class="py-2 border-b">
                                <a href="{{ route('bendahara.pengajuan.show', $item->id_pengajuan) }}" class="text-blue-600 hover:underline">Konfirmasi</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Tidak ada pengajuan menunggu konfirmasi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- SECTION 3: PENCAIRAN BERTAHAP YANG MASIH BERSISA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-amber-500">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-bold text-lg">3. Pencairan Bertahap (Parsial)</h3>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                        {{ $pencairanParsial->count() }} menunggu pembayaran
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="border-b py-2">No. Pengajuan</th>
                                <th class="border-b py-2">Pengaju</th>
                                <th class="border-b py-2">Metode</th>
                                <th class="border-b py-2 text-right">Total</th>
                                <th class="border-b py-2 text-right">Dibayar</th>
                                <th class="border-b py-2 text-right">Sisa</th>
                                <th class="border-b py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pencairanParsial as $item)
                            <tr>
                                <td class="py-2 border-b">{{ $item->no_pengajuan }}</td>
                                <td class="py-2 border-b">{{ $item->user->nama_lengkap ?? $item->user->name ?? '-' }}</td>
                                <td class="py-2 border-b"><span class="bg-gray-200 px-2 py-1 rounded text-xs">{{ $item->metode_label }}</span></td>
                                <td class="py-2 border-b text-right">Rp {{ number_format((float) $item->total_nominal, 0, ',', '.') }}</td>
                                <td class="py-2 border-b text-right text-green-700">Rp {{ number_format($item->total_dibayar, 0, ',', '.') }}</td>
                                <td class="py-2 border-b text-right font-semibold text-red-700">Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}</td>
                                <td class="py-2 border-b">
                                    <a href="{{ route('bendahara.pengajuan.show', $item->id_pengajuan) }}" class="text-blue-600 hover:underline">Catat Pembayaran</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-gray-500">Tidak ada pencairan bertahap yang menunggu pembayaran.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>