<x-app-layout title="Tambah Tim">
    <div class="max-w-2xl rounded-xl border border-ui-border bg-ui-card p-6">
        <form method="POST" action="{{ route('admin.tim.store') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="nama_tim" value="Nama Tim" />
                <x-text-input id="nama_tim" name="nama_tim" value="{{ old('nama_tim') }}" required />
                @error('nama_tim')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="keterangan" value="Keterangan (opsional)" />
                <x-text-input id="keterangan" name="keterangan" value="{{ old('keterangan') }}" />
                @error('keterangan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <x-primary-button>Simpan Tim</x-primary-button>
                <a href="{{ route('admin.tim.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
