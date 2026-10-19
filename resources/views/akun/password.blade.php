<x-app-layout title="Pengaturan Akun">

    <div class="mb-5">
        <h2 class="text-2xl font-bold">Pengaturan Akun</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Kelola data kontak dan keamanan akun Anda.
        </p>
    </div>

    <div class="space-y-6 max-w-xl">

        {{-- ============================================================
             KARTU 1: INFORMASI PROFIL (No. HP / WhatsApp)
        ============================================================ --}}
        <div class="rounded-xl border border-ui-border bg-ui-card p-6">

            <h3 class="text-base font-semibold mb-1">Informasi Profil</h3>
            <p class="text-sm text-ui-muted mb-5">
                Nomor HP digunakan untuk menerima notifikasi WhatsApp terkait pengajuan Anda.
            </p>

            {{-- Pesan sukses --}}
            @if(session('success_profil'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('success_profil') }}
                </div>
            @endif

            {{-- Identitas akun (read-only) --}}
            <dl class="mb-5 grid gap-3 border-b border-ui-border pb-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-ui-muted">Nama</dt>
                    <dd class="mt-1 font-medium">{{ auth()->user()->nama_lengkap ?? auth()->user()->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-ui-muted">Email</dt>
                    <dd class="mt-1">{{ auth()->user()->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-ui-muted">Role</dt>
                    <dd class="mt-1">{{ auth()->user()->role?->nama_role ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-ui-muted">Divisi</dt>
                    <dd class="mt-1">{{ auth()->user()->divisi?->nama_divisi ?? '—' }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('profil.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="no_hp" value="No. HP / WhatsApp" />
                    <x-text-input
                        id="no_hp"
                        name="no_hp"
                        type="tel"
                        class="mt-1 block w-full"
                        value="{{ old('no_hp', auth()->user()->no_hp ?? '') }}"
                        placeholder="Contoh: 081234567890"
                    />
                    <p class="mt-1 text-xs text-ui-muted">
                        Format: angka saja, boleh diawali +62. Kosongkan untuk menghapus.
                    </p>
                    @if ($errors->updateProfil->has('no_hp'))
                        <p class="mt-1 text-sm text-status-rejected">{{ $errors->updateProfil->first('no_hp') }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <x-primary-button>Simpan Nomor HP</x-primary-button>
                </div>
            </form>
        </div>

        {{-- ============================================================
             KARTU 2: GANTI PASSWORD
        ============================================================ --}}
        <div class="rounded-xl border border-ui-border bg-ui-card p-6">

            <h3 class="text-base font-semibold mb-1">Ganti Password</h3>
            <p class="text-sm text-ui-muted mb-5">
                Untuk keamanan, password lama harus dimasukkan lebih dulu.
            </p>

            {{-- Pesan sukses --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="current_password" value="Password Saat Ini" />
                    <x-text-input id="current_password" name="current_password" type="password"
                                  class="mt-1 block w-full" autocomplete="current-password" required />
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" value="Password Baru" />
                    <x-text-input id="password" name="password" type="password"
                                  class="mt-1 block w-full" autocomplete="new-password" required />
                    <p class="mt-1 text-xs text-ui-muted">Minimal 8 karakter.</p>
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Ulangi Password Baru" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                  class="mt-1 block w-full" autocomplete="new-password" required />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <x-primary-button>Simpan Password</x-primary-button>
                </div>
            </form>
        </div>

    </div>

</x-app-layout>
