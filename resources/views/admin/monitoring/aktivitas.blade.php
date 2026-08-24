<x-app-layout title="Log Aktivitas">

    @php
        $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();

        $warnaPeran = [
            'Verifikator' => 'bg-pov-verifikasi/10 text-pov-verifikasi',
            'Bendahara' => 'bg-pov-pencairan/10 text-pov-pencairan',
            'PPK' => 'bg-pov-pengajuan/10 text-pov-pengajuan',
            'PPSPM' => 'bg-status-neutral/10 text-status-neutral',
        ];
    @endphp

    <div class="mb-5">
        <p class="text-sm font-medium text-status-neutral">Super Admin — Pemantauan Menyeluruh</p>
        <h2 class="mt-1 text-2xl font-bold">Log Aktivitas</h2>
        <p class="mt-1 text-sm text-ui-muted">
            Seluruh tindakan yang tercatat dari Verifikator, Bendahara, PPK, dan PPSPM, terbaru lebih dulu.
        </p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.monitoring.aktivitas') }}" class="mb-5 rounded-xl border border-ui-border bg-ui-card p-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="peran" class="block text-xs font-medium uppercase text-ui-muted">Peran</label>
                <select id="peran" name="peran" class="mt-1 block w-full rounded-lg border-ui-border text-sm shadow-sm focus:border-status-neutral focus:ring-status-neutral">
                    <option value="">Semua peran</option>
                    @foreach(['Verifikator', 'Bendahara', 'PPK', 'PPSPM'] as $peran)
                        <option value="{{ $peran }}" @selected(($filter['peran'] ?? '') === $peran)>{{ $peran }}</option>
                    @endforeach
                </select>
            </div>

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

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-status-neutral px-4 py-2 text-sm font-semibold text-ui-card hover:opacity-90">
                Terapkan Filter
            </button>

            @if($adaFilter)
                <a href="{{ route('admin.monitoring.aktivitas') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium hover:bg-ui-page">
                    Reset
                </a>
            @endif

            <p class="ml-auto text-sm text-ui-muted">
                <strong class="text-ui-text">{{ $aktivitas->total() }}</strong> aktivitas tercatat
            </p>
        </div>
    </form>

    {{-- Daftar aktivitas --}}
    <div class="overflow-hidden rounded-xl border border-ui-border bg-ui-card">
        <div class="divide-y divide-ui-border">
            @forelse($aktivitas as $log)
                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $warnaPeran[$log['peran']] ?? 'bg-ui-page text-ui-muted' }}">
                                    {{ $log['peran'] }}
                                </span>
                                <p class="font-semibold">{{ $log['aksi'] }}</p>
                            </div>

                            <p class="mt-1 text-sm text-ui-muted">
                                oleh {{ $log['aktor'] }}
                                @if($log['pengajuan'])
                                    ·
                                    <a href="{{ route('admin.monitoring.show', $log['pengajuan']->id_pengajuan) }}" class="font-medium text-ui-text hover:underline">
                                        {{ $log['pengajuan']->no_pengajuan }}
                                    </a>
                                    <span class="text-ui-muted">({{ $log['pengajuan']->user->nama_lengkap ?? '-' }})</span>
                                @endif
                            </p>

                            @if(!empty($log['dokumen']))
                                <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ui-muted">
                                    @foreach($log['dokumen'] as $label => $nilai)
                                        <span>{{ $label }}: <strong class="font-medium text-ui-text">{{ $nilai }}</strong></span>
                                    @endforeach
                                </p>
                            @endif

                            @if(!empty($log['catatan']))
                                <p class="mt-2 rounded-lg bg-ui-page p-2 text-xs">
                                    <span class="text-ui-muted">Catatan:</span> {{ $log['catatan'] }}
                                </p>
                            @endif
                        </div>

                        <div class="shrink-0 text-right">
                            <x-status-badge :kode="strtoupper($log['status'])" :label="$log['status']" />
                            <p class="mt-1 text-xs text-ui-muted">
                                {{ $log['waktu'] ? $log['waktu']->format('d M Y H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-ui-muted">Tidak ada aktivitas yang cocok dengan filter.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-5">{{ $aktivitas->links() }}</div>

</x-app-layout>
