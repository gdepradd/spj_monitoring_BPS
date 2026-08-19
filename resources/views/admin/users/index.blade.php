<x-app-layout title="Manajemen User">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">User</h2>
            <p class="mt-1 text-sm text-ui-muted">Kelola akun dan role pengguna aplikasi.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-status-neutral px-4 py-2 text-sm font-semibold text-ui-card">+ Tambah User</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Urutan Verifikator</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3">Aksi</th></tr></thead>
                <tbody class="divide-y divide-ui-border">
                @foreach($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->nama_lengkap }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->role->nama_role }}</td>
                        <td class="px-4 py-3">{{ $user->urutan_verifikator ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $user->status_aktif ? 'Ya' : 'Tidak' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-pov-pengajuan hover:underline">Edit</a>
                            @if($user->id_user !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-2 text-status-rejected hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $users->links() }}</div>
</x-app-layout>
