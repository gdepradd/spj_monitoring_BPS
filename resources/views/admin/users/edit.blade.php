@php
    $roleAwal = (string) old('id_role', $user->id_role ?? '');
    $roleAwalVerifikator = $roleAwal !== '' && $roleAwal === (string) $verifikatorRoleId;
@endphp
<x-app-layout title="Edit User">
    <div class="max-w-2xl rounded-xl border border-ui-border bg-ui-card p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5"
                  x-data="{ idRole: @js($roleAwal), get isVerifikator() { return this.idRole === @js((string) $verifikatorRoleId) } }">
            @csrf @method('PUT')

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
        <x-input-label for="no_hp" value="No. HP / WhatsApp" />
        <x-text-input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp', $user->no_hp ?? '') }}" placeholder="Contoh: 081234567890" />
        <p class="mt-1 text-xs text-ui-muted">Digunakan untuk notifikasi WhatsApp. Kosongkan jika tidak diperlukan.</p>
        @error('no_hp')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="id_role" value="Role" />
        <select id="id_role" name="id_role" x-model="idRole" class="mt-1 block w-full rounded-lg border-ui-border bg-ui-card" required>
            <option value="">Pilih role</option>
            @foreach($roles as $role)
                <option value="{{ $role->id_role }}" @selected((string) old('id_role', $user->id_role ?? '') === (string) $role->id_role)>{{ $role->nama_role }}</option>
            @endforeach
        </select>
        @error('id_role')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    {{-- Satu orang boleh masuk beberapa tim sekaligus, jadi pilihannya berupa
         centang, bukan dropdown. Tidak ada centang sama sekali berarti user
         belum tergabung di tim mana pun — melepas seluruh centang memang
         mengeluarkannya dari semua tim. --}}
    <div>
        <x-input-label value="Tim (opsional, boleh lebih dari satu)" />

        @if($timList->isEmpty())
            <p class="mt-1 text-sm text-ui-muted">
                Belum ada tim. Buat dulu di
                <a href="{{ route('admin.tim.index') }}" class="font-semibold text-pov-pengajuan hover:underline">Manajemen Tim</a>.
            </p>
        @else
            @php($timTerpilih = collect(old('tim', $user->tim->pluck('id_tim')->all()))->map(fn ($id) => (string) $id)->all())

            <div class="mt-2 grid gap-2 rounded-lg border border-ui-border p-3 sm:grid-cols-2">
                @foreach($timList as $tim)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="tim[]" value="{{ $tim->id_tim }}"
                               @checked(in_array((string) $tim->id_tim, $timTerpilih, true))
                               class="mt-0.5 rounded border-ui-border text-pov-pengajuan focus:ring-pov-pengajuan">
                        <span>
                            <span class="font-medium">{{ $tim->nama_tim }}</span>
                            @if($tim->keterangan)
                                <span class="block text-xs text-ui-muted">{{ $tim->keterangan }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        @endif

        <p class="mt-1 text-xs text-ui-muted">Rekan satu tim dapat saling melihat progres pengajuan.</p>
        @error('tim')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
        @error('tim.*')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    {{-- Hanya relevan untuk role verifikator: disembunyikan sekaligus di-disable untuk
         role lain supaya field ini tidak ikut terkirim saat form disubmit. --}}
    <div x-show="isVerifikator" @style(['display: none' => ! $roleAwalVerifikator])>
        <x-input-label for="urutan_verifikator" value="Urutan Verifikator (1-3)" />
        <x-text-input id="urutan_verifikator" name="urutan_verifikator" type="number" min="1" max="3" x-bind:disabled="! isVerifikator" :disabled="! $roleAwalVerifikator" value="{{ old('urutan_verifikator', $user->urutan_verifikator ?? '') }}" />
        <p class="mt-1 text-xs text-ui-muted">Menentukan verifikator ini bertugas di tahap ke berapa.</p>
        @error('urutan_verifikator')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-input-label for="password" value="Password" />
        <x-text-input id="password" name="password" type="password"  />
        @if(isset($user))<p class="mt-1 text-xs text-ui-muted">Kosongkan jika password tidak diubah.</p>@endif
        @error('password')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $user->status_aktif ?? true)) class="rounded border-ui-border text-pov-pengajuan focus:ring-pov-pengajuan"> Akun aktif</label>

            <div class="flex gap-3"><x-primary-button>Simpan Perubahan</x-primary-button><a href="{{ route('admin.users.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div>
</x-app-layout>
