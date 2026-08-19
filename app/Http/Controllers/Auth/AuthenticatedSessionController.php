<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
{
    $request->authenticate();

    $request->session()->regenerate();

    $user = $request->user();

    if (! $user->status_aktif) {
        auth()->logout();

        return back()->withErrors([
            'email' => 'Akun Anda tidak aktif.',
        ]);
    }

    $role = $user->role?->nama_role;

    return match ($role) {
        'pegawai' => redirect()->route('pegawai.dashboard'),

        'verifikator' => redirect('/verifikator/dashboard'),

        'ppk' => redirect('/ppk/dashboard'),

        'bendahara' => redirect('/bendahara/dashboard'),

        'ppspm' => redirect('/ppspm/dashboard'),

        'admin' => redirect()->route('admin.users.index'),

        default => redirect()->route('login')
            ->withErrors([
                'email' => 'Role pengguna tidak dikenali.',
            ]),
    };
}

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
