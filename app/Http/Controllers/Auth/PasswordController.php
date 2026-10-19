<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Halaman profil akun sendiri: ubah No. HP dan ganti password.
     * Terbuka untuk semua role.
     */
    public function edit(): View
    {
        return view('akun.password');
    }

    /**
     * Simpan perubahan No. HP / WhatsApp milik akun sendiri.
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updateProfil', [
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{7,20}$/'],
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan angka saja (boleh diawali +).',
            'no_hp.max'   => 'Nomor HP maksimal 20 karakter.',
        ]);

        $request->user()->update(['no_hp' => $validated['no_hp']]);

        return back()->with('success_profil', 'Nomor HP berhasil diperbarui.');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ], [
            'current_password.required'       => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required'               => 'Password baru wajib diisi.',
            'password.min'                    => 'Password baru minimal :min karakter.',
            'password.confirmed'              => 'Ulangi password baru tidak sama dengan password baru.',
            'password.different'              => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
