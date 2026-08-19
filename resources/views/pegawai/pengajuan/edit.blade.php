<x-app-layout title="Perbaiki Pengajuan">
    <div class="max-w-3xl rounded-xl border border-ui-border bg-ui-card p-6">
        <div class="mb-5 rounded-lg border border-status-revisi/20 bg-status-revisi/10 p-4 text-sm">
            Pengajuan ini berstatus <strong>REVISI</strong>. Setelah disimpan, status kembali ke <strong>VERIFIKASI_1</strong> sesuai keputusan default pada issue.
        </div>

        <form method="POST" action="{{ route('pegawai.pengajuan.update', $pengajuan) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="tanggal_pengajuan" value="Tanggal Pengajuan" />
                <x-text-input id="tanggal_pengajuan" name="tanggal_pengajuan" type="date" value="{{ old('tanggal_pengajuan', $pengajuan->tanggal_pengajuan->toDateString()) }}" required />
                @error('tanggal_pengajuan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="perihal" value="Perihal" />
                <x-text-input id="perihal" name="perihal" value="{{ old('perihal', $pengajuan->perihal) }}" required />
                @error('perihal')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="keterangan" value="Keterangan / Ringkasan Dokumen SPJ" />
                <textarea id="keterangan" name="keterangan" rows="5" required class="mt-1 block w-full rounded-lg border-ui-border bg-ui-card focus:border-pov-pengajuan focus:ring-pov-pengajuan">{{ old('keterangan', $pengajuan->keterangan) }}</textarea>
                @error('keterangan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="total_nominal" value="Total Nominal" />
                <x-text-input id="total_nominal" name="total_nominal" type="number" min="0" step="0.01" value="{{ old('total_nominal', $pengajuan->total_nominal) }}" required />
                @error('total_nominal')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="catatan_pengaju" value="Catatan Perbaikan dari Pegawai" />
                <textarea id="catatan_pengaju" name="catatan_pengaju" rows="4" class="mt-1 block w-full rounded-lg border-ui-border bg-ui-card focus:border-pov-pengajuan focus:ring-pov-pengajuan">{{ old('catatan_pengaju', $pengajuan->catatan_pengaju) }}</textarea>
                @error('catatan_pengaju')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <x-primary-button>Kirim Perbaikan</x-primary-button>
                <a href="{{ route('pegawai.pengajuan.show', $pengajuan) }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
