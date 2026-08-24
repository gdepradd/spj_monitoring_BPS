<x-app-layout title="Semua Pengajuan">

    @php
        $rupiah = fn ($nilai) => 'Rp ' . number_format((float) $nilai, 0, ',', '.');

        $metodeLabel = [
            'LS_BENDAHARA' => 'LS Bendahara',
            'LS_PIHAK_KETIGA' => 'LS Pihak Ketiga',
            'UP_TUP' => 'UP / TUP',
        ];

        $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
    @endphp

    <div class="mb-5">
        <p class="text-sm font-medium text-status-neutral">Super Admin — Pemantauan Menyeluruh</p>
        <h2 class="mt-1 text-2xl font-bold">Semua Pengajuan</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Seluruh pengajuan dari semua pegawai. Klik satu baris untuk melihat detail dan seluruh tahapannya.
        </p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.monitoring.pengajuan') }}" class="mb-5 rounded-xl border border-ui-border bg-ui-card p-5">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase text-ui-muted">Cari</label>
                <input type="text" id="q" name="q" value="{{ $filter['q'] ?? '' }}"
                       placeholder="No. pengajuan, pemohon, penerima, tim, atau keterangan"
                       class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
            </div>

            <div>
                <label for="status" class="block text-xs font-medium uppercase text-ui-muted">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
                    <option value="">Semua status</option>
                    @foreach($statusList as $status)
                        <option value="{{ $status->kode_status }}" @selected(($filter['status'] ?? '') === $status->kode_status)>
                            {{ $status->nama_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="metode" class="block text-xs font-medium uppercase text-ui-muted">Metode Pembayaran</label>
                <select id="metode" name="metode" class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
                    <option value="">Semua metode</option>
                    @foreach($metodeLabel as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['metode'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                    <option value="BELUM" @selected(($filter['metode'] ?? '') === 'BELUM')>Belum ditentukan</option>
                </select>
            </div>

            <div>
                <label for="pemohon" class="block text-xs font-medium uppercase text-ui-muted">Pemohon</label>
                <select id="pemohon" name="pemohon" class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
                    <option value="">Semua pemohon</option>
                    @foreach($pemohonList as $pemohon)
                        <option value="{{ $pemohon->id_user }}" @selected((string) ($filter['pemohon'] ?? '') === (string) $pemohon->id_user)>
                            {{ $pemohon->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="dari" class="block text-xs font-medium uppercase text-ui-muted">Dari</label>
                    <input type="date" id="dari" name="dari" value="{{ $filter['dari'] ?? '' }}"
                           class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
                </div>
                <div>
                    <label for="sampai" class="block text-xs font-medium uppercase text-ui-muted">Sampai</label>
                    <input type="date" id="sampai" name="sampai" value="{{ $filter['sampai'] ?? '' }}"
                           class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-status-neutral px-4 py-2 text-sm font-semibold text-ui-card hover:opacity-90">
                Terapkan Filter
            </button>

            @if($adaFilter)
                <a href="{{ route('admin.monitoring.pengajuan') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium hover:bg-ui-page">
                    Reset
                </a>
            @endif

            <p class="ml-auto text-sm text-ui-muted">
                <strong class="text-ui-text">{{ $ringkasan['jumlah'] }}</strong> pengajuan ·
                <strong class="text-ui-text">{{ $rupiah($ringkasan['nominal']) }}</strong>
            </p>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ui-border text-sm">
                <thead class="bg-ui-page text-left text-ui-muted">
                    <tr>
                        <th class="px-4 py-3">No. Pengajuan</th>
                        <th class="px-4 py-3">Pemohon</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Jejak Proses</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ui-border">
                    @forelse($pengajuan as $item)
                        <tr class="hover:bg-ui-page">
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->no_pengajuan }}</p>
                                <p class="mt-1 text-xs text-ui-muted">{{ $item->tim_pengaju ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ $item->user->nama_lengkap ?? '-' }}</p>
                                <p class="mt-1 text-xs text-ui-muted">Penerima: {{ $item->nama_penerima ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">{{ $rupiah($item->total_nominal) }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs">{{ $metodeLabel[$item->metode_pembayaran] ?? 'Belum ditentukan' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :kode="$item->status?->kode_status" :label="$item->status?->nama_status" />
                            </td>
                            <td class="px-4 py-3 text-xs text-ui-muted">
                                Verifikasi {{ $item->verifikasi_count }} ·
                                Bendahara {{ $item->bendahara_count }} ·
                                PPK {{ $item->ppk_count }} ·
                                PPSPM {{ $item->ppspm_count }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.monitoring.show', $item->id_pengajuan) }}"
                                   class="inline-flex rounded-lg bg-status-neutral px-3 py-1 text-xs font-semibold text-ui-card hover:opacity-90">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-ui-muted">
                                Tidak ada pengajuan yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $pengajuan->links() }}</div>

</x-app-layout>
