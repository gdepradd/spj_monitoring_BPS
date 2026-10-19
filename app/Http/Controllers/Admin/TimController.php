<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TimController extends Controller
{
    public function index(): View
    {
        $tim = Tim::withCount('users')->orderBy('nama_tim')->paginate(15);

        return view('admin.tim.index', compact('tim'));
    }

    public function create(): View
    {
        return view('admin.tim.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Tim::create($this->validateTim($request));

        return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil dibuat.');
    }

    public function edit(Tim $tim): View
    {
        $tim->load(['users' => fn ($query) => $query->orderBy('nama_lengkap')]);

        return view('admin.tim.edit', compact('tim'));
    }

    public function update(Request $request, Tim $tim): RedirectResponse
    {
        $tim->update($this->validateTim($request, $tim));

        return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Tim $tim): RedirectResponse
    {
        // Baris pivot tim_user memakai cascadeOnDelete, sehingga penghapusan tidak
        // akan gagal walau tim masih beranggota — anggotanya hanya kehilangan tim
        // itu secara diam-diam. Karena itu penghapusan ditahan di sini supaya
        // admin sadar dan memindahkan anggotanya lebih dulu.
        $jumlahUser = $tim->users()->count();

        if ($jumlahUser > 0) {
            return back()->with(
                'error',
                "Tim tidak dapat dihapus karena masih beranggota {$jumlahUser} user. Keluarkan anggotanya lebih dulu."
            );
        }

        $tim->delete();

        return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil dihapus.');
    }

    private function validateTim(Request $request, ?Tim $tim = null): array
    {
        return $request->validate([
            'nama_tim' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tim', 'nama_tim')->ignore($tim?->id_tim, 'id_tim'),
            ],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_tim.required' => 'Nama tim wajib diisi.',
            'nama_tim.unique' => 'Nama tim tersebut sudah ada.',
        ]);
    }
}
