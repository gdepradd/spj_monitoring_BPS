<x-app-layout title="Detail Pengajuan">
    <div class="grid gap-6 xl:grid-cols-3">
        <section class="xl:col-span-2">
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-ui-muted">Nomor Pengajuan</p>
                        <h2 class="text-xl font-bold">{{ $pengajuan->no_pengajuan }}</h2>
                    </div>
                    <x-status-badge :kode="$pengajuan->status->kode_status" :label="$pengajuan->status->nama_status" />
                </div>

                <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div><dt class="text-xs font-medium uppercase text-ui-muted">Tanggal</dt><dd class="mt-1">{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</dd></div>
                    <div><dt class="text-xs font-medium uppercase text-ui-muted">Nominal</dt><dd class="mt-1 font-semibold">Rp {{ number_format((float) $pengajuan->total_nominal, 2, ',', '.') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase text-ui-muted">Perihal</dt><dd class="mt-1">{{ $pengajuan->perihal }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase text-ui-muted">Keterangan</dt><dd class="mt-1 whitespace-pre-line">{{ $pengajuan->keterangan }}</dd></div>
                    @if($pengajuan->catatan_pengaju)
                        <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase text-ui-muted">Catatan Pengaju</dt><dd class="mt-1 whitespace-pre-line">{{ $pengajuan->catatan_pengaju }}</dd></div>
                    @endif
                </dl>

                @if($pengajuan->status->kode_status === 'REVISI')
                    <a href="{{ route('pegawai.pengajuan.edit', $pengajuan) }}" class="mt-6 inline-flex rounded-lg bg-status-revisi px-4 py-2 text-sm font-semibold text-ui-card">Perbaiki Pengajuan</a>
                @endif
            </div>
        </section>

        <aside>
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">
                <h3 class="font-semibold">Timeline Status</h3>
                <div class="mt-5">
                    @forelse($timeline as $item)
                        <x-timeline-item
                            :judul="$item['judul']"
                            :kode-status="$item['kode_status']"
                            :nama-status="$item['nama_status']"
                            :waktu="$item['waktu']"
                            :catatan="$item['catatan']"
                            :aktor="$item['aktor']"
                        />
                    @empty
                        <p class="text-sm text-ui-muted">Belum ada riwayat proses.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
