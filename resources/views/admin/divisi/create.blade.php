<x-app-layout title="Tambah Divisi">
    <div class="max-w-2xl rounded-xl border border-ui-border bg-ui-card p-6">
        <form method="POST" action="{{ route('admin.divisi.store') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="nama_divisi" value="Nama Divisi" />
                <x-text-input id="nama_divisi" name="nama_divisi" value="{{ old('nama_divisi') }}" required />
                @error('nama_divisi')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="keterangan" value="Keterangan (opsional)" />
                <x-text-input id="keterangan" name="keterangan" value="{{ old('keterangan') }}" />
                @error('keterangan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <x-primary-button>Simpan Divisi</x-primary-button>
                <a href="{{ route('admin.divisi.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
