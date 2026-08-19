<x-app-layout title="Tambah User">
    <div class="max-w-2xl rounded-xl border border-ui-border bg-ui-card p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

    <div>
        <x-input-label for="nama_lengkap" value="Nama Lengkap" />
        <x-text-input id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap ?? '') }}" required />
        @error('nama_lengkap')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required />
        @error('email')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="no_hp" value="No. HP" />
        <x-text-input id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp ?? '') }}" required />
        @error('no_hp')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="id_role" value="Role" />
        <select id="id_role" name="id_role" class="mt-1 block w-full rounded-lg border-ui-border bg-ui-card" required>
            <option value="">Pilih role</option>
            @foreach($roles as $role)
                <option value="{{ $role->id_role }}" @selected((string) old('id_role', $user->id_role ?? '') === (string) $role->id_role)>{{ $role->nama_role }}</option>
            @endforeach
        </select>
        @error('id_role')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="urutan_verifikator" value="Urutan Verifikator (1-3, hanya untuk role verifikator)" />
        <x-text-input id="urutan_verifikator" name="urutan_verifikator" type="number" min="1" max="3" value="{{ old('urutan_verifikator', $user->urutan_verifikator ?? '') }}" />
        @error('urutan_verifikator')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="password" value="Password" />
        <x-text-input id="password" name="password" type="password" required />
        @if(isset($user))<p class="mt-1 text-xs text-ui-muted">Kosongkan jika password tidak diubah.</p>@endif
        @error('password')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $user->status_aktif ?? true)) class="rounded border-ui-border text-pov-pengajuan focus:ring-pov-pengajuan"> Akun aktif</label>

            <div class="flex gap-3"><x-primary-button>Simpan User</x-primary-button><a href="{{ route('admin.users.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div>
</x-app-layout>
