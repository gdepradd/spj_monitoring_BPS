<x-app-layout title="Detail Riwayat Pengajuan">

    @php
        $kodeStatus = $pengajuan->status?->kode_status;

        $statusClass = match (true) {
            $kodeStatus === 'SELESAI' => 'border-green-200 bg-green-100 text-green-700',
            // Sudah ada uang keluar tapi belum lunas; dibedakan dari amber
            // yang berarti masih menunggu diproses.
            $kodeStatus === 'PENCAIRAN_PARSIAL' => 'border-sky-200 bg-sky-100 text-sky-700',
            $kodeStatus === 'REVISI' => 'border-orange-200 bg-orange-100 text-orange-700',
            $kodeStatus === 'DITOLAK' => 'border-red-200 bg-red-100 text-red-700',
            default => 'border-amber-200 bg-amber-100 text-amber-700',
        };

        // Tombol kembali menyesuaikan role yang sedang login.
        $role = auth()->user()?->role?->nama_role;

        $rutKembali = match ($role) {
            'verifikator' => route('verifikator.riwayat'),
            'bendahara' => route('bendahara.riwayat'),
            'ppk' => route('ppk.riwayat'),
            'ppspm' => route('ppspm.riwayat'),
            'pegawai' => route('pengajuan.index'),
            'admin' => route('admin.monitoring.pengajuan'),
            default => url()->previous(),
        };
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Detail Riwayat Pengajuan</h2>
            <p class="mt-1 text-sm text-ui-muted">
                Seluruh tahapan pengajuan {{ $pengajuan->no_pengajuan }} dari verifikasi hingga pencairan.
            </p>
        </div>

        <a href="{{ $rutKembali }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium hover:bg-ui-page">
            &larr; Kembali
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">

        {{-- ============================================================ --}}
        {{-- KOLOM KIRI: INFORMASI + DOKUMEN --}}
        {{-- ============================================================ --}}
        <section class="space-y-6 xl:col-span-2">

            {{-- Informasi Pengajuan --}}
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-ui-muted">Nomor Pengajuan</p>
                        <h3 class="mt-1 text-xl font-bold">{{ $pengajuan->no_pengajuan }}</h3>
                    </div>

                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                        {{ $pengajuan->status?->nama_status ?? '-' }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Pemohon</dt>
                        <dd class="mt-1">{{ $pengajuan->user->nama_lengkap ?? $pengajuan->user->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Tanggal Pengajuan</dt>
                        <dd class="mt-1">{{ $pengajuan->tanggal_pengajuan ? \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->translatedFormat('d F Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Tim Pengaju</dt>
                        <dd class="mt-1">{{ $pengajuan->tim_pengaju ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Bulan Kegiatan</dt>
                        <dd class="mt-1">{{ $pengajuan->bulan_kegiatan ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Nama Penerima</dt>
                        <dd class="mt-1">{{ $pengajuan->nama_penerima ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Total Nominal</dt>
                        <dd class="mt-1 font-semibold">Rp {{ number_format((float) $pengajuan->total_nominal, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Metode Pembayaran</dt>
                        <dd class="mt-1 font-semibold text-purple-700">{{ $pengajuan->metode_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Jenis Pencairan</dt>
                        <dd class="mt-1">{{ $pengajuan->jenis_pencairan_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Sudah Dibayarkan</dt>
                        <dd class="mt-1 font-semibold">
                            Rp {{ number_format($pengajuan->total_dibayar, 0, ',', '.') }}
                            <span class="text-xs font-normal text-ui-muted">({{ $pengajuan->persen_dibayar }}%)</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Sisa Pembayaran</dt>
                        <dd class="mt-1 font-semibold">Rp {{ number_format($pengajuan->sisa_pembayaran, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">Alur Pencairan</dt>
                        <dd class="mt-1 text-sm">
                            @if($pengajuan->metode_pembayaran === 'UP_TUP')
                                Bendahara &rarr; Pembayaran Langsung
                            @elseif($pengajuan->metode_pembayaran)
                                Bendahara &rarr; PPK &rarr; PPSPM &rarr; Konfirmasi Bendahara
                            @else
                                Belum ditentukan
                            @endif
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
                </dl>
            </div>

            {{-- Dokumen Pencairan --}}
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <h3 class="font-semibold">Dokumen Pencairan</h3>
                <p class="mt-1 text-sm text-ui-muted">Nomor dan tanggal dokumen yang diterbitkan pada setiap tahap.</p>

                @if(!$spp && !$pembayaranLangsung && !$ppk && !$ppspm && !$konfirmasi)
                    <div class="mt-5 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-ui-muted">
                        Belum ada dokumen pencairan yang diterbitkan untuk pengajuan ini.
                    </div>
                @else
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ui-border text-xs uppercase text-ui-muted">
                                    <th class="py-2 pr-4">Dokumen</th>
                                    <th class="py-2 pr-4">Nomor</th>
                                    <th class="py-2 pr-4">Tanggal</th>
                                    <th class="py-2">Diterbitkan Oleh</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ui-border">
                                @if($spp)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium">SPP (Bendahara)</td>
                                        <td class="py-2 pr-4">{{ $spp->no_spp ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $spp->tgl_spp ? \Carbon\Carbon::parse($spp->tgl_spp)->format('d M Y') : '-' }}</td>
                                        <td class="py-2">{{ $spp->user->nama_lengkap ?? $spp->user->name ?? 'Bendahara' }}</td>
                                    </tr>
                                @endif

                                @if($ppk)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium">Persetujuan SPP (PPK)</td>
                                        <td class="py-2 pr-4">{{ $ppk->no_spm ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $ppk->tgl_spm ? \Carbon\Carbon::parse($ppk->tgl_spm)->format('d M Y') : ($ppk->tanggal_proses ? \Carbon\Carbon::parse($ppk->tanggal_proses)->format('d M Y') : '-') }}</td>
                                        <td class="py-2">{{ $ppk->user->nama_lengkap ?? $ppk->user->name ?? 'PPK' }}</td>
                                    </tr>
                                @endif

                                @if($ppspm)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium">SPM (PPSPM)</td>
                                        <td class="py-2 pr-4">{{ $ppspm->no_spm ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $ppspm->tgl_spm ? \Carbon\Carbon::parse($ppspm->tgl_spm)->format('d M Y') : '-' }}</td>
                                        <td class="py-2">{{ $ppspm->user->nama_lengkap ?? $ppspm->user->name ?? 'PPSPM' }}</td>
                                    </tr>
                                @endif

                                @php $transfer = $konfirmasi ?? $pembayaranLangsung; @endphp

                                {{-- SP2D diterbitkan pada tahap konfirmasi Bendahara --}}
                                @if($konfirmasi && $konfirmasi->no_sp2d)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium">SP2D (Bendahara)</td>
                                        <td class="py-2 pr-4">{{ $konfirmasi->no_sp2d }}</td>
                                        <td class="py-2 pr-4">{{ $konfirmasi->tgl_sp2d ? \Carbon\Carbon::parse($konfirmasi->tgl_sp2d)->format('d M Y') : '-' }}</td>
                                        <td class="py-2">{{ $konfirmasi->user->nama_lengkap ?? $konfirmasi->user->name ?? 'Bendahara' }}</td>
                                    </tr>
                                @endif

                                @if($transfer)
                                    @php $tunai = $transfer->cara_bayar === 'TUNAI'; @endphp
                                    <tr>
                                        <td class="py-2 pr-4 font-medium">
                                            {{ $tunai ? 'Pembayaran Tunai' : 'Transfer / Pencairan' }}
                                        </td>
                                        <td class="py-2 pr-4">{{ $tunai ? ($transfer->no_kwitansi ?? '-') : ($transfer->no_sp2d ?? '-') }}</td>
                                        <td class="py-2 pr-4">{{ $transfer->tgl_transfer ? \Carbon\Carbon::parse($transfer->tgl_transfer)->format('d M Y') : '-' }}</td>
                                        <td class="py-2">{{ $transfer->user->nama_lengkap ?? $transfer->user->name ?? 'Bendahara' }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($transfer ?? false)
                        <dl class="mt-5 grid gap-5 sm:grid-cols-3">
                            <div>
                                <dt class="text-xs font-medium uppercase text-ui-muted">Cara Bayar</dt>
                                <dd class="mt-1">{{ $transfer->cara_bayar === 'TUNAI' ? 'Tunai' : 'Transfer' }}</dd>
                            </div>

                            @if($transfer->cara_bayar === 'TUNAI')
                                <div>
                                    <dt class="text-xs font-medium uppercase text-ui-muted">No. Kwitansi</dt>
                                    <dd class="mt-1">{{ $transfer->no_kwitansi ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase text-ui-muted">Tanggal Dibayarkan</dt>
                                    <dd class="mt-1">{{ $transfer->tgl_transfer ? \Carbon\Carbon::parse($transfer->tgl_transfer)->format('d M Y') : '-' }}</dd>
                                </div>
                            @else
                                <div>
                                    <dt class="text-xs font-medium uppercase text-ui-muted">Tanggal Transfer</dt>
                                    <dd class="mt-1">{{ $transfer->tgl_transfer ? \Carbon\Carbon::parse($transfer->tgl_transfer)->format('d M Y') : '-' }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                @endif
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- KOLOM KANAN: TIMELINE LENGKAP --}}
        {{-- ============================================================ --}}
        <aside>
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <h3 class="font-semibold">Riwayat Proses</h3>
                <p class="mt-1 text-xs text-ui-muted">Urutan tahapan beserta pelaku, waktu, dokumen, dan catatan.</p>

                <ol class="mt-6 space-y-5">
                    @foreach($pengajuan->riwayat_lengkap as $item)
                        @php
                            $status = $item['status'] ?? '-';

                            [$dotClass, $textClass] = match ($status) {
                                'Selesai' => ['bg-green-500', 'text-green-600'],
                                'Sedang Diproses' => ['bg-blue-500', 'text-blue-600'],
                                'Revisi' => ['bg-orange-500', 'text-orange-600'],
                                'Ditolak' => ['bg-red-500', 'text-red-600'],
                                default => ['bg-gray-300', 'text-gray-500'],
                            };
                        @endphp

                        <li class="flex gap-3">
                            <span class="mt-1.5 inline-block h-3 w-3 shrink-0 rounded-full {{ $dotClass }}"></span>

                            <div class="min-w-0 flex-1">
                                <p class="font-semibold">{{ $item['judul'] }}</p>
                                <p class="mt-0.5 text-xs text-ui-muted">
                                    {{ $item['aktor'] }}
                                    @if($item['waktu'])
                                        &middot; {{ \Carbon\Carbon::parse($item['waktu'])->format('d M Y H:i') }}
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs font-medium {{ $textClass }}">Status: {{ $status }}</p>

                                @if(!empty($item['dokumen']))
                                    <dl class="mt-2 space-y-1 rounded-lg bg-ui-page p-3 text-xs">
                                        @foreach($item['dokumen'] as $label => $nilai)
                                            <div class="flex justify-between gap-3">
                                                <dt class="text-ui-muted">{{ $label }}</dt>
                                                <dd class="text-right font-medium">
                                                    @if($nilai instanceof \Carbon\CarbonInterface)
                                                        {{ $nilai->format('d M Y') }}
                                                    @elseif(is_string($nilai) && preg_match('/^\d{4}-\d{2}-\d{2}/', $nilai))
                                                        {{ \Carbon\Carbon::parse($nilai)->format('d M Y') }}
                                                    @else
                                                        {{ $nilai }}
                                                    @endif
                                                </dd>
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
                </ol>
            </div>
        </aside>
    </div>
</x-app-layout>
