<x-app-layout title="Dashboard Bendahara">

    @php
        $rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');

        $metodeLabel = [
            'LS_BENDAHARA' => 'LS Bendahara',
            'LS_PIHAK_KETIGA' => 'LS Pihak Ketiga',
            'UP_TUP' => 'UP / TUP',
        ];

        $persenCair = $nominalAcc > 0 ? round($nominalCair / $nominalAcc * 100, 1) : 0;
    @endphp

    <div class="mb-6">
        <p class="text-sm font-medium text-pov-pencairan">POV 3 — Pencairan</p>
        <h2 class="mt-1 text-2xl font-bold">Dashboard Bendahara</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Ringkasan nominal pencairan dan pengajuan yang menunggu tindakan Bendahara.
        </p>
    </div>

    {{-- Filter metode pembayaran --}}
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <span class="text-sm text-ui-muted">Metode:</span>

        <a href="{{ route('bendahara.dashboard') }}"
           class="rounded-lg border px-3 py-1.5 text-sm font-medium {{ $metode ? 'border-ui-border hover:bg-ui-page' : 'border-pov-pencairan bg-pov-pencairan text-white' }}">
            Semua
        </a>

        @foreach($metodeLabel as $kode => $label)
            <a href="{{ route('bendahara.dashboard', ['metode' => $kode]) }}"
               class="rounded-lg border px-3 py-1.5 text-sm font-medium {{ $metode === $kode ? 'border-pov-pencairan bg-pov-pencairan text-white' : 'border-ui-border hover:bg-ui-page' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Resume nominal --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Sudah di-ACC Bendahara</p>
            <p class="mt-2 text-2xl font-bold text-ui-text">{{ $rupiah($nominalAcc) }}</p>
            <p class="mt-3 text-xs text-ui-muted">{{ $jumlahAcc }} pengajuan sudah ditentukan metodenya.</p>
        </div>

        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Sudah Dicairkan</p>
            <p class="mt-2 text-2xl font-bold text-status-done">{{ $rupiah($nominalCair) }}</p>
            <p class="mt-3 text-xs text-ui-muted">{{ $persenCair }}% dari nilai yang di-ACC.</p>
        </div>

        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Belum Dicairkan</p>
            <p class="mt-2 text-2xl font-bold text-status-pending">{{ $rupiah($nominalBelumCair) }}</p>
            <p class="mt-3 text-xs text-ui-muted">Termasuk sisa pembayaran bertahap.</p>
        </div>

        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Menunggu Proses</p>
            <p class="mt-2 text-2xl font-bold text-pov-pencairan">{{ $totalMenunggu }}</p>
            <p class="mt-3 text-xs text-ui-muted">Pengajuan menunggu keputusan atau konfirmasi.</p>
        </div>
    </div>

    {{-- Progres pencairan --}}
    @if($nominalAcc > 0)
        <div class="mt-4 rounded-xl border border-ui-border bg-ui-card p-5">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium">Progres pencairan{{ $metode ? ' — ' . $metodeLabel[$metode] : '' }}</span>
                <span class="text-ui-muted">{{ $rupiah($nominalCair) }} / {{ $rupiah($nominalAcc) }}</span>
            </div>
            <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-ui-page">
                <div class="h-full rounded-full bg-status-done" style="width: {{ $persenCair }}%"></div>
            </div>
        </div>
    @endif

    {{-- Pencairan bertahap yang masih berjalan --}}
    <div class="mt-6 rounded-xl border border-ui-border bg-ui-card shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ui-border px-5 py-4">
            <div>
                <h3 class="font-semibold">Pencairan Bertahap Berjalan</h3>
                <p class="mt-1 text-sm text-ui-muted">
                    Pengajuan dengan metode parsial yang masih menyisakan pembayaran.
                </p>
            </div>
            <span class="rounded-full bg-status-pending/10 px-3 py-1 text-xs font-semibold text-status-pending">
                {{ $pencairanParsial->count() }} pengajuan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-right">Dibayar</th>
                        <th class="px-5 py-3 text-right">Sisa</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @forelse($pencairanParsial as $item)
                        <tr class="hover:bg-ui-page">
                            <td class="px-5 py-3 font-semibold">{{ $item->no_pengajuan }}</td>
                            <td class="px-5 py-3">{{ $item->user->nama_lengkap ?? '-' }}</td>
                            <td class="px-5 py-3 text-xs">{{ $metodeLabel[$item->metode_pembayaran] ?? '-' }}</td>
                            <td class="px-5 py-3 text-right">{{ $rupiah($item->total_nominal) }}</td>
                            <td class="px-5 py-3 text-right text-status-done">{{ $rupiah($item->total_dibayar) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-status-rejected">{{ $rupiah($item->sisa_pembayaran) }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('bendahara.pengajuan.show', $item->id_pengajuan) }}"
                                   class="inline-flex rounded-lg bg-pov-pencairan px-3 py-1 text-xs font-semibold text-white hover:opacity-90">
                                    Catat Pembayaran
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-ui-muted">
                                Tidak ada pencairan bertahap yang menunggu pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Rincian per metode --}}
    <div class="mt-6 rounded-xl border border-ui-border bg-ui-card shadow-sm">
        <div class="border-b border-ui-border px-5 py-4">
            <h3 class="font-semibold">Rincian per Metode Pembayaran</h3>
            <p class="mt-1 text-sm text-ui-muted">Nilai yang di-ACC dibanding yang sudah benar-benar cair.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3 text-right">Pengajuan</th>
                        <th class="px-5 py-3 text-right">Nilai di-ACC</th>
                        <th class="px-5 py-3 text-right">Sudah Cair</th>
                        <th class="px-5 py-3 text-right">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @forelse($perMetode as $baris)
                        <tr class="hover:bg-ui-page">
                            <td class="px-5 py-3">
                                <a href="{{ route('bendahara.dashboard', ['metode' => $baris->metode_pembayaran]) }}"
                                   class="font-medium hover:underline">
                                    {{ $metodeLabel[$baris->metode_pembayaran] ?? $baris->metode_pembayaran }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-right">{{ $baris->jumlah }}</td>
                            <td class="px-5 py-3 text-right">{{ $rupiah($baris->nominal) }}</td>
                            <td class="px-5 py-3 text-right text-status-done">{{ $rupiah($baris->cair) }}</td>
                            <td class="px-5 py-3 text-right">{{ $rupiah(max(0, $baris->nominal - $baris->cair)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-ui-muted">Belum ada pengajuan yang di-ACC.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tautan cepat --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('bendahara.pengajuan.index') }}"
           class="rounded-lg bg-pov-pencairan px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
            Daftar Pengajuan
        </a>
        <a href="{{ route('bendahara.riwayat') }}"
           class="rounded-lg border border-ui-border px-5 py-2.5 text-sm font-medium hover:bg-ui-page">
            Riwayat Proses
        </a>
    </div>

</x-app-layout>
