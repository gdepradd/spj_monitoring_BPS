<x-app-layout title="Detail Pengajuan">

    @php
        $rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');
        $tanggal = fn ($nilai, $format = 'd M Y') => $nilai ? \Carbon\Carbon::parse($nilai)->format($format) : '-';

        $statusPencairanLabel = fn ($id) => match ((int) $id) {
            \App\Models\StatusPencairan::ACC => 'ACC',
            \App\Models\StatusPencairan::REVISI => 'Revisi',
            \App\Models\StatusPencairan::DITOLAK => 'Ditolak',
            default => 'Selesai',
        };

        $tahapBendaharaLabel = [
            'PENGAJUAN_SPP' => 'Pengajuan SPP',
            'PEMBAYARAN_LANGSUNG' => 'Pembayaran Langsung (UP/TUP)',
            'KONFIRMASI' => 'Konfirmasi Pencairan',
        ];

        $jeda = function ($jam) {
            if ($jam === null) return null;
            if ($jam < 1) return 'kurang dari 1 jam';
            if ($jam < 48) return $jam . ' jam';
            return round($jam / 24) . ' hari';
        };
    @endphp

    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-status-neutral">Super Admin — Detail Menyeluruh</p>
            <h2 class="mt-1 text-2xl font-bold">{{ $pengajuan->no_pengajuan }}</h2>
            <p class="mt-1 text-sm text-ui-muted">
                Diajukan {{ $tanggal($pengajuan->tanggal_pengajuan) }} oleh
                {{ $pengajuan->user->nama_lengkap ?? '-' }}
                @if($tahapSekarang)
                    · saat ini <strong class="text-ui-text">{{ $tahapSekarang }}</strong>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <x-status-badge :kode="$pengajuan->status?->kode_status" :label="$pengajuan->status?->nama_status" />
            <a href="{{ route('admin.monitoring.pengajuan') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium hover:bg-ui-page">
                &larr; Kembali
            </a>
        </div>
    </div>

    {{-- Peringatan hanya-baca --}}
    <div class="mb-5 rounded-lg border border-ui-border bg-ui-page px-4 py-3 text-sm text-ui-muted">
        Halaman ini hanya untuk pemantauan. Super admin tidak dapat menyetujui, menolak, atau mengubah tahapan pengajuan.
    </div>

    <div class="grid gap-6 xl:grid-cols-3">

        <section class="space-y-6 xl:col-span-2">

            {{-- Informasi pengajuan --}}
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <h3 class="font-semibold">Informasi Pengajuan</h3>

                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Pemohon</dt>
                        <dd class="mt-1">
                            {{ $pengajuan->user->nama_lengkap ?? '-' }}
                            <span class="block text-xs text-ui-muted">
                                {{ $pengajuan->user->email ?? '-' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Tim Pengaju</dt>
                        <dd class="mt-1">{{ $pengajuan->tim_pengaju ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Tanggal Kegiatan</dt>
                        <dd class="mt-1">{{ $tanggal($pengajuan->bulan_kegiatan) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Nama Penerima</dt>
                        <dd class="mt-1">{{ $pengajuan->nama_penerima ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Total Nominal</dt>
                        <dd class="mt-1 text-lg font-bold">{{ $rupiah($pengajuan->total_nominal) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Metode Pembayaran</dt>
                        <dd class="mt-1 font-semibold text-pov-pencairan">{{ $pengajuan->metode_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Jenis Pencairan</dt>
                        <dd class="mt-1">{{ $pengajuan->jenis_pencairan_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Sudah Dibayarkan</dt>
                        <dd class="mt-1 font-semibold text-status-done">
                            {{ $rupiah($pengajuan->total_dibayar) }}
                            <span class="text-xs font-normal text-ui-muted">({{ $pengajuan->persen_dibayar }}%)</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Sisa Pembayaran</dt>
                        <dd class="mt-1 font-semibold {{ $pengajuan->sisa_pembayaran > 0 ? 'text-status-pending' : 'text-ui-muted' }}">
                            {{ $rupiah($pengajuan->sisa_pembayaran) }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase text-ui-muted">Keterangan</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $pengajuan->keterangan ?? '-' }}</dd>
                    </div>
                    @if($pengajuan->catatan_pengaju)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase text-ui-muted">Catatan Pengaju</dt>
                            <dd class="mt-1 whitespace-pre-line">{{ $pengajuan->catatan_pengaju }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Dibuat</dt>
                        <dd class="mt-1">{{ $tanggal($pengajuan->created_at, 'd M Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Pembaruan Terakhir</dt>
                        <dd class="mt-1">{{ $tanggal($pengajuan->updated_at, 'd M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Log verifikasi --}}
            <div class="rounded-xl border border-ui-border bg-ui-card">
                <div class="border-b border-ui-border px-6 py-4">
                    <h3 class="font-semibold">Log Verifikasi ({{ $pengajuan->verifikasi->count() }})</h3>
                    <p class="mt-1 text-sm text-ui-muted">Seluruh keputusan verifikator, termasuk pengulangan setelah revisi.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ui-border text-sm">
                        <thead class="bg-ui-page text-left text-ui-muted">
                            <tr>
                                <th class="px-6 py-3">Tahap</th>
                                <th class="px-6 py-3">Verifikator</th>
                                <th class="px-6 py-3">Waktu</th>
                                <th class="px-6 py-3">Hasil</th>
                                <th class="px-6 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ui-border">
                            @forelse($pengajuan->verifikasi as $v)
                                <tr>
                                    <td class="px-6 py-3 font-medium">Tahap {{ $v->tahap }}</td>
                                    <td class="px-6 py-3">{{ $v->verifikator->nama_lengkap ?? '-' }}</td>
                                    <td class="px-6 py-3">{{ $tanggal($v->tanggal_verifikasi ?? $v->created_at, 'd M Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        <x-status-badge :kode="$v->statusVerifikasi?->kode_status" :label="$v->statusVerifikasi?->nama_status" />
                                    </td>
                                    <td class="px-6 py-3 text-ui-muted">{{ $v->catatan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-6 text-center text-ui-muted">Belum ada proses verifikasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Log bendahara --}}
            <div class="rounded-xl border border-ui-border bg-ui-card">
                <div class="border-b border-ui-border px-6 py-4">
                    <h3 class="font-semibold">Log Bendahara ({{ $pengajuan->bendahara->count() }})</h3>
                    <p class="mt-1 text-sm text-ui-muted">Pemilihan metode, penerbitan SPP, sampai konfirmasi transfer.</p>
                </div>

                <div class="divide-y divide-ui-border">
                    @forelse($pengajuan->bendahara as $b)
                        <div class="px-6 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $tahapBendaharaLabel[$b->tahap] ?? ($b->tahap ?? 'Keputusan Bendahara') }}</p>
                                    <p class="mt-1 text-xs text-ui-muted">
                                        {{ $b->user->nama_lengkap ?? '-' }} · {{ $tanggal($b->tanggal_proses, 'd M Y H:i') }}
                                    </p>
                                </div>
                                <x-status-badge :kode="strtoupper($statusPencairanLabel($b->id_status))" :label="$statusPencairanLabel($b->id_status)" />
                            </div>

                            @php
                                $dok = array_filter([
                                    'Nominal' => $b->nominal !== null ? $rupiah($b->nominal) : null,
                                    'Penerima' => $b->nama_penerima ?? $b->atas_nama_rekening,
                                    'No. SPP' => $b->no_spp,
                                    'Tgl. SPP' => $b->tgl_spp ? $tanggal($b->tgl_spp) : null,
                                    'No. SPM' => $b->no_spm,
                                    'No. SP2D' => $b->no_sp2d,
                                    'Tgl. SP2D' => $b->tgl_sp2d ? $tanggal($b->tgl_sp2d) : null,
                                    'Cara Bayar' => $b->cara_bayar === 'TUNAI' ? 'Tunai' : ($b->cara_bayar ? 'Transfer' : null),
                                    ($b->cara_bayar === 'TUNAI' ? 'Tgl. Dibayarkan' : 'Tgl. Transfer') => $b->tgl_transfer ? $tanggal($b->tgl_transfer) : null,
                                    'No. Kwitansi' => $b->no_kwitansi,
                                    'Nama Bank' => $b->nama_bank,
                                    'No. Rekening' => $b->no_rekening,
                                    'Atas Nama' => $b->atas_nama_rekening,
                                ]);
                            @endphp

                            @if($dok)
                                <dl class="mt-3 grid gap-3 rounded-lg bg-ui-page p-3 text-xs sm:grid-cols-3">
                                    @foreach($dok as $label => $nilai)
                                        <div>
                                            <dt class="text-ui-muted">{{ $label }}</dt>
                                            <dd class="mt-0.5 font-medium">{{ $nilai }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif

                            @if($b->catatan)
                                <p class="mt-3 text-sm"><span class="text-ui-muted">Catatan:</span> {{ $b->catatan }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="px-6 py-6 text-center text-sm text-ui-muted">Belum ada proses bendahara.</p>
                    @endforelse
                </div>
            </div>

            {{-- Log PPK & PPSPM --}}
            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-xl border border-ui-border bg-ui-card">
                    <div class="border-b border-ui-border px-5 py-4">
                        <h3 class="font-semibold">Log PPK ({{ $pengajuan->ppk->count() }})</h3>
                    </div>
                    <div class="divide-y divide-ui-border">
                        @forelse($pengajuan->ppk as $p)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">Persetujuan SPP</p>
                                        <p class="mt-1 text-xs text-ui-muted">
                                            {{ $p->user->nama_lengkap ?? '-' }} · {{ $tanggal($p->tanggal_proses, 'd M Y H:i') }}
                                        </p>
                                    </div>
                                    <x-status-badge :kode="strtoupper($statusPencairanLabel($p->id_status))" :label="$statusPencairanLabel($p->id_status)" />
                                </div>
                                @if($p->no_spm || $p->tgl_spm)
                                    <p class="mt-2 text-xs text-ui-muted">
                                        No. SPM: <span class="font-medium text-ui-text">{{ $p->no_spm ?? '-' }}</span> ·
                                        Tgl: <span class="font-medium text-ui-text">{{ $tanggal($p->tgl_spm) }}</span>
                                    </p>
                                @endif
                                @if($p->catatan)
                                    <p class="mt-2 text-sm"><span class="text-ui-muted">Catatan:</span> {{ $p->catatan }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="px-5 py-6 text-center text-sm text-ui-muted">Belum diproses PPK.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-ui-border bg-ui-card">
                    <div class="border-b border-ui-border px-5 py-4">
                        <h3 class="font-semibold">Log PPSPM ({{ $pengajuan->ppspm->count() }})</h3>
                    </div>
                    <div class="divide-y divide-ui-border">
                        @forelse($pengajuan->ppspm as $p)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">Pengajuan ke Kemenkeu</p>
                                        <p class="mt-1 text-xs text-ui-muted">
                                            {{ $p->user->nama_lengkap ?? '-' }} · {{ $tanggal($p->tanggal_proses, 'd M Y H:i') }}
                                        </p>
                                    </div>
                                    <x-status-badge :kode="strtoupper($statusPencairanLabel($p->id_status))" :label="$statusPencairanLabel($p->id_status)" />
                                </div>
                                <dl class="mt-3 grid grid-cols-2 gap-3 rounded-lg bg-ui-page p-3 text-xs">
                                    <div><dt class="text-ui-muted">Tgl. Kemenkeu</dt><dd class="mt-0.5 font-medium">{{ $tanggal($p->tgl_ajukan_kemenkeu) }}</dd></div>
                                    <div><dt class="text-ui-muted">No. SPM</dt><dd class="mt-0.5 font-medium">{{ $p->no_spm ?? '-' }}</dd></div>
                                    <div><dt class="text-ui-muted">Tgl. SPM</dt><dd class="mt-0.5 font-medium">{{ $tanggal($p->tgl_spm) }}</dd></div>
                                </dl>
                                <p class="mt-2 text-xs text-ui-muted">SP2D dicatat Bendahara pada tahap konfirmasi.</p>
                                @if($p->catatan)
                                    <p class="mt-2 text-sm"><span class="text-ui-muted">Catatan:</span> {{ $p->catatan }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="px-5 py-6 text-center text-sm text-ui-muted">Belum diproses PPSPM.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- Kronologi --}}
        <aside>
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <h3 class="font-semibold">Kronologi Proses</h3>
                <p class="mt-1 text-xs text-ui-muted">Urut waktu, lengkap dengan jeda antar tindakan.</p>

                <ol class="mt-6 space-y-5">
                    <li class="flex gap-3">
                        <span class="mt-1.5 inline-block h-3 w-3 shrink-0 rounded-full bg-pov-pengajuan"></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">Pengajuan Dibuat</p>
                            <p class="mt-0.5 text-xs text-ui-muted">
                                {{ $pengajuan->user->nama_lengkap ?? '-' }} ·
                                {{ $tanggal($pengajuan->created_at, 'd M Y H:i') }}
                            </p>
                        </div>
                    </li>

                    @foreach($langkah as $item)
                        @php
                            [$dot, $teks] = match ($item['status']) {
                                'ACC' => ['bg-status-approved', 'text-status-approved'],
                                'Revisi' => ['bg-status-revisi', 'text-status-revisi'],
                                'Ditolak' => ['bg-status-rejected', 'text-status-rejected'],
                                default => ['bg-status-done', 'text-status-done'],
                            };
                        @endphp
                        <li class="flex gap-3">
                            <span class="mt-1.5 inline-block h-3 w-3 shrink-0 rounded-full {{ $dot }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold">{{ $item['peran'] }} — {{ $item['aksi'] }}</p>
                                <p class="mt-0.5 text-xs text-ui-muted">
                                    {{ $item['aktor'] }}
                                    @if($item['waktu']) · {{ $item['waktu']->format('d M Y H:i') }} @endif
                                    @if($jeda($item['jeda_jam'])) · +{{ $jeda($item['jeda_jam']) }} @endif
                                </p>
                                <p class="mt-0.5 text-xs font-medium {{ $teks }}">{{ $item['status'] }}</p>

                                @if(!empty($item['dokumen']))
                                    <dl class="mt-2 space-y-1 rounded-lg bg-ui-page p-3 text-xs">
                                        @foreach($item['dokumen'] as $label => $nilai)
                                            <div class="flex justify-between gap-3">
                                                <dt class="text-ui-muted">{{ $label }}</dt>
                                                <dd class="text-right font-medium">{{ $nilai }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif

                                @if(!empty($item['catatan']))
                                    <div class="mt-2 rounded-lg bg-ui-page p-3 text-xs">
                                        <p class="font-semibold uppercase tracking-wide text-ui-muted">Catatan</p>
                                        <p class="mt-1 whitespace-pre-line">{{ $item['catatan'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach

                    @if($pengajuan->status?->kode_status === 'SELESAI')
                        <li class="flex gap-3">
                            <span class="mt-1.5 inline-block h-3 w-3 shrink-0 rounded-full bg-status-done"></span>
                            <div>
                                <p class="font-semibold">Pencairan Selesai</p>
                                <p class="mt-0.5 text-xs text-ui-muted">{{ $tanggal($pengajuan->updated_at, 'd M Y H:i') }}</p>
                            </div>
                        </li>
                    @endif
                </ol>
            </div>
        </aside>
    </div>

</x-app-layout>
