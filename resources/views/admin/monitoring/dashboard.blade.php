<x-app-layout title="Dashboard Monitoring">

    @php
        $rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');

        $metodeLabel = [
            'LS_BENDAHARA' => 'LS Bendahara',
            'LS_PIHAK_KETIGA' => 'LS Pihak Ketiga',
            'UP_TUP' => 'UP / TUP',
        ];
    @endphp

    <div class="mb-6">
        <p class="text-sm font-medium text-status-neutral">Super Admin — Pemantauan Menyeluruh</p>
        <h2 class="mt-1 text-2xl font-bold text-ui-text">Dashboard Monitoring</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Ringkasan seluruh pengajuan dari semua pegawai, lintas tahap verifikasi hingga pencairan.
        </p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Total Pengajuan</p>
            <p class="mt-2 text-3xl font-bold text-ui-text">{{ $total }}</p>
            <p class="mt-3 text-xs text-ui-muted">Nilai total {{ $rupiah($totalNominal) }}</p>
        </div>

        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Sedang Berjalan</p>
            <p class="mt-2 text-3xl font-bold text-status-pending">{{ $berjalan }}</p>
            <p class="mt-3 text-xs text-ui-muted">Termasuk {{ $revisi }} pengajuan berstatus revisi.</p>
        </div>

        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Selesai Cair</p>
            <p class="mt-2 text-3xl font-bold text-status-done">{{ $selesai }}</p>
            <p class="mt-3 text-xs text-ui-muted">Nilai cair {{ $rupiah($nominalCair) }}</p>
        </div>

        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <p class="text-sm font-medium text-ui-muted">Ditolak</p>
            <p class="mt-2 text-3xl font-bold text-status-rejected">{{ $ditolak }}</p>
            <p class="mt-3 text-xs text-ui-muted">Pengajuan yang dihentikan pada salah satu tahap.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        {{-- Beban tiap tahap --}}
        <div class="rounded-xl border border-ui-border bg-ui-card shadow-sm xl:col-span-2">
            <div class="border-b border-ui-border px-5 py-4">
                <h3 class="font-semibold text-ui-text">Posisi Pengajuan Saat Ini</h3>
                <p class="mt-1 text-sm text-ui-muted">Jumlah pengajuan yang sedang menunggu tindakan di tiap tahap.</p>
            </div>

            <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($beban as $item)
                    <a href="{{ route('admin.monitoring.pengajuan', ['status' => $item['kode']]) }}"
                       class="flex items-center justify-between rounded-xl border border-ui-border p-4 transition hover:border-status-neutral/40 hover:bg-ui-page">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ui-text">{{ $item['label'] }}</p>
                            <p class="mt-1 text-xs text-ui-muted">{{ str_replace('_', ' ', $item['kode']) }}</p>
                        </div>
                        <p class="ml-3 text-2xl font-bold {{ $item['jumlah'] > 0 ? 'text-status-pending' : 'text-ui-muted' }}">
                            {{ $item['jumlah'] }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Metode pembayaran --}}
        <div class="rounded-xl border border-ui-border bg-ui-card shadow-sm">
            <div class="border-b border-ui-border px-5 py-4">
                <h3 class="font-semibold text-ui-text">Metode Pembayaran</h3>
                <p class="mt-1 text-sm text-ui-muted">Sebaran jalur pencairan yang dipilih Bendahara.</p>
            </div>

            <div class="space-y-3 p-5">
                @forelse($metode as $m)
                    <div class="flex items-center justify-between rounded-lg border border-ui-border p-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium">{{ $metodeLabel[$m->metode_pembayaran] ?? 'Belum Ditentukan' }}</p>
                            <p class="mt-1 text-xs text-ui-muted">{{ $rupiah($m->nominal) }}</p>
                        </div>
                        <p class="ml-3 text-xl font-bold text-ui-text">{{ $m->jumlah }}</p>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-ui-muted">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Pengajuan paling lama berjalan --}}
        <div class="rounded-xl border border-ui-border bg-ui-card shadow-sm">
            <div class="flex items-center justify-between border-b border-ui-border px-5 py-4">
                <div>
                    <h3 class="font-semibold text-ui-text">Paling Lama Berjalan</h3>
                    <p class="mt-1 text-sm text-ui-muted">Pengajuan aktif yang tertua, perlu ditindaklanjuti.</p>
                </div>
                <a href="{{ route('admin.monitoring.pengajuan') }}" class="shrink-0 text-sm font-semibold text-status-neutral hover:underline">Lihat semua</a>
            </div>

            <div class="divide-y divide-ui-border">
                @forelse($terlama as $item)
                    <a href="{{ route('admin.monitoring.show', $item->id_pengajuan) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-ui-page">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $item->no_pengajuan }}</p>
                            <p class="mt-1 truncate text-xs text-ui-muted">
                                {{ $item->user->nama_lengkap ?? '-' }} · {{ $rupiah($item->total_nominal) }}
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <x-status-badge :kode="$item->status?->kode_status" :label="$item->status?->nama_status" />
                            <p class="mt-1 text-xs text-ui-muted">
                                {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->diffInDays(now()) }} hari
                            </p>
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-ui-muted">Tidak ada pengajuan yang sedang berjalan.</p>
                @endforelse
            </div>
        </div>

        {{-- Aktivitas terbaru --}}
        <div class="rounded-xl border border-ui-border bg-ui-card shadow-sm">
            <div class="flex items-center justify-between border-b border-ui-border px-5 py-4">
                <div>
                    <h3 class="font-semibold text-ui-text">Aktivitas Terbaru</h3>
                    <p class="mt-1 text-sm text-ui-muted">Tindakan terakhir dari seluruh role.</p>
                </div>
                <a href="{{ route('admin.monitoring.aktivitas') }}" class="shrink-0 text-sm font-semibold text-status-neutral hover:underline">Log lengkap</a>
            </div>

            <div class="divide-y divide-ui-border">
                @forelse($aktivitasTerbaru as $log)
                    <div class="px-5 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold">
                                    <span class="text-status-neutral">{{ $log['peran'] }}</span> · {{ $log['aksi'] }}
                                </p>
                                <p class="mt-1 truncate text-xs text-ui-muted">
                                    {{ $log['aktor'] }}
                                    @if($log['pengajuan'])
                                        ·
                                        <a href="{{ route('admin.monitoring.show', $log['pengajuan']->id_pengajuan) }}" class="font-medium hover:underline">
                                            {{ $log['pengajuan']->no_pengajuan }}
                                        </a>
                                    @endif
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <x-status-badge :kode="strtoupper($log['status'])" :label="$log['status']" />
                                <p class="mt-1 text-xs text-ui-muted">
                                    {{ $log['waktu'] ? $log['waktu']->format('d M Y H:i') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-ui-muted">Belum ada aktivitas tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Rincian per status --}}
    <div class="mt-6 rounded-xl border border-ui-border bg-ui-card shadow-sm">
        <div class="border-b border-ui-border px-5 py-4">
            <h3 class="font-semibold text-ui-text">Rincian per Status</h3>
            <p class="mt-1 text-sm text-ui-muted">Jumlah dan nilai pengajuan pada setiap status.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @foreach($statusCounts as $status)
                        <tr class="hover:bg-ui-page">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.monitoring.pengajuan', ['status' => $status->kode_status]) }}">
                                    <x-status-badge :kode="$status->kode_status" :label="$status->nama_status" />
                                </a>
                            </td>
                            <td class="px-5 py-3 text-ui-muted">{{ $status->keterangan }}</td>
                            <td class="px-5 py-3 text-right font-semibold">{{ $status->jumlah }}</td>
                            <td class="px-5 py-3 text-right">{{ $rupiah($status->nominal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
