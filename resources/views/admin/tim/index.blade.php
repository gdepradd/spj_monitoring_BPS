<x-app-layout title="Manajemen Tim">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">Tim</h2>
            <p class="mt-1 text-sm text-ui-muted">Kelola daftar tim. Satu pegawai boleh masuk beberapa tim, dan rekan satu tim dapat saling melihat progres pengajuan.</p>
        </div>
        <a href="{{ route('admin.tim.create') }}" class="rounded-lg bg-status-neutral px-4 py-2 text-sm font-semibold text-ui-card">+ Tambah Tim</a>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-status-rejected/30 bg-status-rejected/10 px-4 py-3 text-sm text-status-rejected">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-4 py-3">Nama Tim</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Jumlah Anggota</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @forelse($tim as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $item->nama_tim }}</td>
                            <td class="px-4 py-3 text-ui-muted">{{ $item->keterangan ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $item->users_count }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.tim.edit', $item) }}" class="text-pov-pengajuan hover:underline">Edit</a>
                                <form action="{{ route('admin.tim.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tim ini?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-2 text-status-rejected hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-ui-muted">Belum ada tim.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $tim->links() }}</div>
</x-app-layout>
