<x-app-layout title="Ajukan Pembayaran">
    <div class="max-w-3xl rounded-xl border border-ui-border bg-ui-card p-6">
        <h2 class="text-xl font-bold">Form Pengajuan Pembayaran</h2>
        <div class="mt-4 rounded-lg border border-status-pending/20 bg-status-pending/10 p-4 text-sm text-ui-text">
            <strong>Catatan:</strong> versi aplikasi ini tidak menggunakan upload berkas. Tuliskan ringkasan dokumen SPJ pada kolom keterangan.
        </div>

        <form method="POST" action="{{ route('pegawai.pengajuan.store') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-input-label for="tanggal_pengajuan" value="Tanggal Pengajuan" />
                <x-text-input id="tanggal_pengajuan" name="tanggal_pengajuan" type="date" value="{{ old('tanggal_pengajuan', now()->toDateString()) }}" required />
                @error('tanggal_pengajuan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="perihal" value="Perihal" />
                <x-text-input id="perihal" name="perihal" value="{{ old('perihal') }}" required />
                @error('perihal')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="keterangan" value="Keterangan / Ringkasan Dokumen SPJ" />
                <textarea id="keterangan" name="keterangan" rows="5" required class="mt-1 block w-full rounded-lg border-ui-border bg-ui-card focus:border-pov-pengajuan focus:ring-pov-pengajuan">{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="total_nominal" value="Total Nominal" />
                <x-text-input id="total_nominal" name="total_nominal" type="number" min="0" step="0.01" value="{{ old('total_nominal') }}" required />
                @error('total_nominal')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <x-primary-button>Kirim Pengajuan</x-primary-button>
                <a href="{{ route('pegawai.pengajuan.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
