<x-app-layout title="Dashboard Pegawai">

    {{-- Header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-pov-pengajuan">
                POV 1 — Pengajuan
            </p>

            <h2 class="mt-1 text-2xl font-bold text-ui-text">
                Dashboard Pengajuan
            </h2>

            <p class="mt-1 text-sm text-ui-muted">
                Selamat datang, {{ auth()->user()->nama_lengkap }}.
                Pantau perkembangan pengajuan pembayaran Anda di sini.
            </p>
        </div>

        <a href="{{ route('pegawai.pengajuan.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-pov-pengajuan px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>

            Buat Pengajuan
        </a>
    </div>


    {{-- Statistik utama --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total --}}
        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ui-muted">
                        Total Pengajuan
                    </p>

                    <p class="mt-2 text-3xl font-bold text-ui-text">
                        {{ $total }}
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-pov-pengajuan/10 text-pov-pengajuan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-ui-muted">
                Seluruh pengajuan yang pernah Anda buat.
            </p>
        </div>


        {{-- Diproses --}}
        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ui-muted">
                        Masih Diproses
                    </p>

                    <p class="mt-2 text-3xl font-bold text-status-pending">
                        {{ $aktif }}
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-status-pending/10 text-status-pending">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-ui-muted">
                Pengajuan yang sedang melalui proses verifikasi atau pencairan.
            </p>
        </div>


        {{-- Revisi --}}
        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ui-muted">
                        Perlu Revisi
                    </p>

                    <p class="mt-2 text-3xl font-bold text-status-revisi">
                        {{ $revisi }}
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-status-revisi/10 text-status-revisi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-ui-muted">
                Pengajuan yang perlu diperbaiki dan diajukan kembali.
            </p>
        </div>


        {{-- Selesai --}}
        <div class="rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ui-muted">
                        Selesai
                    </p>

                    <p class="mt-2 text-3xl font-bold text-status-done">
                        {{ $selesai }}
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-status-done/10 text-status-done">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-ui-muted">
                Pengajuan yang sudah menyelesaikan seluruh proses.
            </p>
        </div>

    </div>


    {{-- Detail jumlah per status --}}
    <div class="mt-6 rounded-xl border border-ui-border bg-ui-card shadow-sm">

        <div class="border-b border-ui-border px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-ui-text">
                        Status Pengajuan
                    </h3>

                    <p class="mt-1 text-sm text-ui-muted">
                        Ringkasan jumlah pengajuan berdasarkan tahap proses.
                    </p>
                </div>

                <a href="{{ route('pegawai.pengajuan.index') }}"
                    class="text-sm font-semibold text-pov-pengajuan hover:underline">
                    Lihat semua
                </a>
            </div>
        </div>


        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3">

            @forelse($statusCounts as $status)
                <a href="{{ route('pegawai.pengajuan.index', ['status' => $status->kode_status]) }}"
                    class="group flex items-center justify-between rounded-xl border border-ui-border p-4 transition hover:border-pov-pengajuan/40 hover:bg-ui-page">
                    <div>
                        <x-status-badge :kode="$status->kode_status" :label="$status->nama_status" />

                        <p class="mt-2 text-xs text-ui-muted">
                            {{ $status->keterangan }}
                        </p>
                    </div>

                    <div class="ml-4 text-right">
                        <p class="text-2xl font-bold text-ui-text">
                            {{ $status->jumlah }}
                        </p>

                        <p class="text-xs text-ui-muted">
                            Pengajuan
                        </p>
                    </div>
                </a>

            @empty

                <div class="col-span-full py-6 text-center">
                    <p class="text-sm text-ui-muted">
                        Belum ada data status pengajuan.
                    </p>
                </div>
            @endforelse

        </div>
    </div>


    {{-- Menu cepat --}}
    <div class="mt-6">

        <div class="mb-4">
            <h3 class="font-semibold text-ui-text">
                Menu Cepat
            </h3>

            <p class="mt-1 text-sm text-ui-muted">
                Akses fitur utama modul pengajuan.
            </p>
        </div>


        <div class="grid gap-4 md:grid-cols-3">

            {{-- Ajukan --}}
            <a href="{{ route('pegawai.pengajuan.create') }}"
                class="group rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm transition hover:border-pov-pengajuan/40">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-pov-pengajuan/10 text-pov-pengajuan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>

                <h4 class="mt-4 font-semibold text-ui-text group-hover:text-pov-pengajuan">
                    Buat Pengajuan
                </h4>

                <p class="mt-1 text-sm text-ui-muted">
                    Ajukan pembayaran baru dengan mengisi data pengajuan.
                </p>
            </a>


            {{-- Daftar --}}
            <a href="{{ route('pegawai.pengajuan.index') }}"
                class="group rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm transition hover:border-pov-pengajuan/40">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-pov-pengajuan/10 text-pov-pengajuan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>

                <h4 class="mt-4 font-semibold text-ui-text group-hover:text-pov-pengajuan">
                    Daftar Pengajuan
                </h4>

                <p class="mt-1 text-sm text-ui-muted">
                    Lihat seluruh pengajuan dan pantau status prosesnya.
                </p>
            </a>


            {{-- Riwayat --}}
            <a href="{{ route('pegawai.pengajuan.riwayat') }}"
                class="group rounded-xl border border-ui-border bg-ui-card p-5 shadow-sm transition hover:border-pov-pengajuan/40">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-pov-pengajuan/10 text-pov-pengajuan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h4 class="mt-4 font-semibold text-ui-text group-hover:text-pov-pengajuan">
                    Riwayat Pengajuan
                </h4>

                <p class="mt-1 text-sm text-ui-muted">
                    Lihat riwayat pengajuan termasuk yang selesai dan ditolak.
                </p>
            </a>

        </div>

    </div>

</x-app-layout>
