<x-app-layout title="Ganti Password">

    <div class="mb-5">
        <h2 class="text-2xl font-bold">Ganti Password</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Ubah password akun Anda sendiri. Untuk keamanan, password lama harus dimasukkan lebih dulu.
        </p>
    </div>

    <div class="max-w-xl rounded-xl border border-ui-border bg-ui-card p-6">

        {{-- Identitas akun, supaya pengguna yakin sedang mengubah akun yang benar --}}
        <dl class="mb-6 grid gap-4 border-b border-ui-border pb-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase text-ui-muted">Nama</dt>
                <dd class="mt-1 font-medium">{{ auth()->user()->nama_lengkap ?? auth()->user()->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-ui-muted">Email</dt>
                <dd class="mt-1">{{ auth()->user()->email }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
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

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button>Simpan Password</x-primary-button>

                <a href="{{ url('/') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium hover:bg-ui-page">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-app-layout>
