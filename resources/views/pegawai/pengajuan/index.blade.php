<x-app-layout title="Daftar Pengajuan">
    @php
        /*
        |--------------------------------------------------------------------------
        | Tautan pengurutan kolom
        |--------------------------------------------------------------------------
        |
        | Mengklik kolom yang sedang aktif membalik arahnya; kolom lain selalu
        | dimulai dari terbesar/terbaru. Filter status ikut terbawa karena
        | fullUrlWithQuery mempertahankan query lain, sedangkan nomor halaman
        | direset agar hasil urutan baru dibaca dari halaman pertama.
        */
        $tautanUrut = function (string $kolom) use ($urut, $arah) {
            $arahBaru = ($urut === $kolom && $arah === 'desc') ? 'asc' : 'desc';

            return request()->fullUrlWithQuery([
                'urut' => $kolom,
                'arah' => $arahBaru,
                'page' => null,
            ]);
        };

        $panahUrut = fn (string $kolom) => $urut === $kolom
            ? ($arah === 'asc' ? '↑' : '↓')
            : '';
    @endphp

    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            {{-- Pilihan urutan dibawa serta agar tidak hilang saat filter diterapkan. --}}
            <input type="hidden" name="urut" value="{{ $urut }}">
            <input type="hidden" name="arah" value="{{ $arah }}">

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
        <a href="{{ route('pengajuan.create') }}" class="rounded-lg bg-pov-pengajuan px-4 py-2 text-sm font-semibold text-ui-card">+ Ajukan Pembayaran</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-ui-border text-xs font-semibold uppercase text-ui-muted">
                <th class="px-6 py-4">No. Pengajuan</th>
                <th class="px-6 py-4">
                    <a href="{{ $tautanUrut('tanggal_pengajuan') }}" class="inline-flex items-center gap-1 hover:text-ui-text">
                        Tanggal Pengajuan <span class="text-pov-pengajuan">{{ $panahUrut('tanggal_pengajuan') }}</span>
                    </a>
                </th>
                <th class="px-6 py-4">
                    <a href="{{ $tautanUrut('tanggal_selesai') }}" class="inline-flex items-center gap-1 hover:text-ui-text">
                        Tanggal Selesai <span class="text-pov-pengajuan">{{ $panahUrut('tanggal_selesai') }}</span>
                    </a>
                </th>
                <th class="px-6 py-4">
                    <a href="{{ $tautanUrut('total_nominal') }}" class="inline-flex items-center gap-1 hover:text-ui-text">
                        Nominal <span class="text-pov-pengajuan">{{ $panahUrut('total_nominal') }}</span>
                    </a>
                </th>
                {{-- Kolom ini membuat pencairan bertahap terbaca tanpa harus membuka
                     detail: sebelumnya baris yang sudah dibayar sebagian tampak
                     sama persis dengan yang belum dibayar sama sekali. --}}
                <th class="px-6 py-4">Sudah Dibayar</th>
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
                    @php
                        $dibayar = $item->total_dibayar;
                        $persen = $item->persen_dibayar;
                    @endphp

                    @if($dibayar <= 0)
                        <span class="text-ui-muted">—</span>
                    @else
                        <p class="font-medium text-ui-text">Rp {{ number_format($dibayar, 0, ',', '.') }}</p>

                        <div class="mt-1.5 h-1.5 w-24 overflow-hidden rounded-full bg-ui-border">
                            <div
                                class="h-full rounded-full {{ $persen >= 100 ? 'bg-green-500' : 'bg-sky-500' }}"
                                style="width: {{ $persen }}%"
                            ></div>
                        </div>

                        <p class="mt-1 text-xs text-ui-muted">
                            {{ rtrim(rtrim(number_format($persen, 1, ',', '.'), '0'), ',') }}%
                            @if($item->sisa_pembayaran > 0)
                                · sisa Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}
                            @endif
                        </p>
                    @endif
                </td>
                <td class="px-6 py-5">
                    <span
                        @class([
                            'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-amber-100 text-amber-700' => in_array($item->status?->kode_status, [
                                'DIAJUKAN', 'VERIFIKASI_1', 'VERIFIKASI_2', 'VERIFIKASI_3', 
                                'MENUNGGU_PENCAIRAN', 'PROSES_PPK', 'PROSES_PPSPM', 'PROSES_KONFIRMASI_BENDAHARA'
                            ]),
                            // Sudah ada uang keluar tapi belum lunas: dibedakan dari
                            // status "masih menunggu" yang berwarna amber.
                            'bg-sky-100 text-sky-700' => $item->status?->kode_status === 'PENCAIRAN_PARSIAL',
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
                        <a href="{{ route('pengajuan.show', $item) }}" class="font-medium text-blue-600 hover:underline">
                            Detail
                        </a>
                        @if($item->status?->kode_status === 'REVISI')
                            <a href="{{ route('pengajuan.edit', $item) }}" class="font-medium text-orange-600 hover:underline">
                                Perbaiki
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-sm text-ui-muted">
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
