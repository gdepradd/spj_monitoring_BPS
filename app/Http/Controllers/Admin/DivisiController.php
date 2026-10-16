<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisiController extends Controller
{
    public function index(): View
    {
        $divisi = Divisi::withCount('users')->orderBy('nama_divisi')->paginate(15);

        return view('admin.divisi.index', compact('divisi'));
    }

    public function create(): View
    {
        return view('admin.divisi.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Divisi::create($this->validateDivisi($request));

        return redirect()->route('admin.divisi.index')->with('success', 'Divisi berhasil dibuat.');
    }

    public function edit(Divisi $divisi): View
    {
        return view('admin.divisi.edit', compact('divisi'));
    }

    public function update(Request $request, Divisi $divisi): RedirectResponse
    {
        $divisi->update($this->validateDivisi($request, $divisi));

        return redirect()->route('admin.divisi.index')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Divisi $divisi): RedirectResponse
    {
        // Kolom users.id_divisi memakai nullOnDelete, sehingga penghapusan tidak akan
        // gagal walau divisi masih dipakai — user hanya kehilangan divisinya secara
        // diam-diam. Karena itu penghapusan ditahan di sini supaya admin sadar.
        $jumlahUser = $divisi->users()->count();

        if ($jumlahUser > 0) {
            return back()->with(
                'error',
                "Divisi tidak dapat dihapus karena masih dipakai oleh {$jumlahUser} user. Pindahkan user tersebut lebih dulu."
            );
        }

        $divisi->delete();

        return redirect()->route('admin.divisi.index')->with('success', 'Divisi berhasil dihapus.');
    }

    private function validateDivisi(Request $request, ?Divisi $divisi = null): array
    {
        return $request->validate([
            'nama_divisi' => [
                'required',
                'string',
                'max:100',
                Rule::unique('divisi', 'nama_divisi')->ignore($divisi?->id_divisi, 'id_divisi'),
            ],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique' => 'Nama divisi tersebut sudah ada.',
        ]);
    }
}
