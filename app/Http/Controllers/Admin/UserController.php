<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->orderBy('nama_lengkap')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('nama_role')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);
        $validated['password'] = Hash::make($validated['password']);
        $validated['status_aktif'] = $request->boolean('status_aktif');

        $role = Role::findOrFail($validated['id_role']);
        $validated['urutan_verifikator'] = $role->nama_role === 'verifikator'
            ? $validated['urutan_verifikator']
            : null;

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('nama_role')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['status_aktif'] = $request->boolean('status_aktif');
        $role = Role::findOrFail($validated['id_role']);
        $validated['urutan_verifikator'] = $role->nama_role === 'verifikator'
            ? $validated['urutan_verifikator']
            : null;

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id_user === auth()->id()) {
            return back()->with('error', 'Akun yang sedang dipakai tidak dapat dihapus.');
        }

        try {
            $user->delete();
        } catch (QueryException) {
            return back()->with('error', 'User tidak dapat dihapus karena sudah memiliki data transaksi. Nonaktifkan akun sebagai gantinya.');
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id_user, 'id_user'),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'no_hp' => ['required', 'string', 'max:30'],
            'id_role' => ['required', 'exists:roles,id_role'],
            'status_aktif' => ['nullable', 'boolean'],
            'urutan_verifikator' => [
                Rule::requiredIf(function () use ($request) {
                    return Role::find($request->input('id_role'))?->nama_role === 'verifikator';
                }),
                'integer',
                'between:1,3',
            ],
        ]);
    }
}
