<x-app-layout title="Edit Tim">
    <div class="max-w-2xl rounded-xl border border-ui-border bg-ui-card p-6">
        <form method="POST" action="{{ route('admin.tim.update', $tim) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <x-input-label for="nama_tim" value="Nama Tim" />
                <x-text-input id="nama_tim" name="nama_tim" value="{{ old('nama_tim', $tim->nama_tim) }}" required />
                @error('nama_tim')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="keterangan" value="Keterangan (opsional)" />
                <x-text-input id="keterangan" name="keterangan" value="{{ old('keterangan', $tim->keterangan) }}" />
                @error('keterangan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <x-primary-button>Simpan Perubahan</x-primary-button>
                <a href="{{ route('admin.tim.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>

    {{-- Keanggotaan diatur dari halaman user, karena satu orang bisa masuk
         beberapa tim sekaligus. Daftar di sini hanya untuk memastikan. --}}
    <div class="mt-5 max-w-2xl rounded-xl border border-ui-border bg-ui-card p-6">
        <h3 class="font-semibold text-ui-text">Anggota Tim ({{ $tim->users->count() }})</h3>
        <p class="mt-1 text-sm text-ui-muted">
            Keanggotaan diatur per user di
            <a href="{{ route('admin.users.index') }}" class="font-semibold text-pov-pengajuan hover:underline">Manajemen User</a>,
            karena satu orang boleh tergabung di beberapa tim.
        </p>

        @if($tim->users->isEmpty())
            <p class="mt-3 text-sm text-ui-muted">Belum ada anggota.</p>
        @else
            <ul class="mt-3 space-y-1 text-sm">
                @foreach($tim->users as $anggota)
                    <li class="flex items-center justify-between gap-3 border-b border-ui-border py-1.5 last:border-0">
                        <span>{{ $anggota->nama_lengkap }}</span>
                        <a href="{{ route('admin.users.edit', $anggota) }}" class="text-xs font-semibold text-pov-pengajuan hover:underline">Ubah tim</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
