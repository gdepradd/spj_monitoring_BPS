<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
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
        $users = User::with(['role', 'tim'])->orderBy('nama_lengkap')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('nama_role')->get();
        $verifikatorRoleId = $roles->firstWhere('nama_role', 'verifikator')?->id_role;
        $timList = Tim::orderBy('nama_tim')->get();

        return view('admin.users.create', compact('roles', 'verifikatorRoleId', 'timList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);
        // Keanggotaan tim tersimpan di tabel pivot, bukan kolom users,
        // jadi dikeluarkan dari data yang dipakai User::create().
        $tim = $this->timTerpilih($validated);
        $validated['password'] = Hash::make($validated['password']);
        $validated['status_aktif'] = $request->boolean('status_aktif');

        $role = Role::findOrFail($validated['id_role']);
        $validated['urutan_verifikator'] = $role->nama_role === 'verifikator'
            ? $validated['urutan_verifikator']
            : null;

        User::create($validated)->tim()->sync($tim);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('nama_role')->get();
        $verifikatorRoleId = $roles->firstWhere('nama_role', 'verifikator')?->id_role;
        $timList = Tim::orderBy('nama_tim')->get();
        $user->load('tim');

        return view('admin.users.edit', compact('user', 'roles', 'verifikatorRoleId', 'timList'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);
        $tim = $this->timTerpilih($validated);

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
        // sync() sekaligus mengeluarkan user dari tim yang centangnya dilepas.
        $user->tim()->sync($tim);

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
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{7,20}$/'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'id_role' => ['required', 'exists:roles,id_role'],
            // Tim bersifat opsional dan boleh lebih dari satu. Tidak ada centang
            // sama sekali berarti user tidak tergabung di tim mana pun.
            'tim' => ['nullable', 'array'],
            'tim.*' => ['integer', 'exists:tim,id_tim'],
            'status_aktif' => ['nullable', 'boolean'],
            // Hanya relevan untuk role verifikator. 'nullable' wajib ada: middleware
            // ConvertEmptyStringsToNull mengubah input kosong jadi null, dan tanpa
            // 'nullable' rule 'integer' menolak null sehingga role lain ikut terblokir.
            'urutan_verifikator' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return Role::find($request->input('id_role'))?->nama_role === 'verifikator';
                }),
                'integer',
                'between:1,3',
            ],
        ]);
    }

    /**
     * Ambil pilihan tim dari data tervalidasi, sekaligus mengeluarkannya dari
     * array supaya tidak ikut diteruskan ke kolom tabel users.
     *
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function timTerpilih(array &$validated): array
    {
        $tim = $validated['tim'] ?? [];
        unset($validated['tim']);

        return array_map('intval', array_unique($tim));
    }
}
