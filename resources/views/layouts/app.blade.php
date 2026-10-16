@props(['title' => 'Sistem Monitoring Pencairan'])

@php
    $user = auth()->user();
    $role = $user?->role?->nama_role;

    // Label peran, ditambah nama divisi bila akun ini punya divisi.
    // Contoh hasil: "Pegawai Divisi Produksi".
    $roleLabel = $role === 'admin'
        ? 'Super Admin'
        : ucwords(str_replace('_', ' ', (string) $role));

    if ($user?->divisi) {
        $roleLabel .= ' Divisi ' . $user->divisi->nama_divisi;
    }

    $sidebarClass = match (true) {
        $role === 'pegawai' => 'bg-pov-pengajuan',
        in_array($role, ['verifikator', 'verifikator_1', 'verifikator_2', 'verifikator_3']) => 'bg-pov-verifikasi',
        in_array($role, ['ppk', 'bendahara', 'ppspm']) => 'bg-pov-pencairan',
        default => 'bg-status-neutral',
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/webp" href="{{ asset('aset/bps-icon.webp') }}">
    <title>{{ $title }} - Sistem Monitoring Pencairan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ui-page text-ui-text antialiased">
<div class="min-h-screen md:flex">
    <aside class="{{ $sidebarClass }} w-full text-ui-card md:min-h-screen md:w-72">
        <div class="border-b border-ui-card/20 px-6 py-5">
            {{-- Logo berteks gelap di atas latar transparan, sedangkan sidebar berwarna
                 pekat dan berbeda-beda per role. Alas putih menjaga logo tetap terbaca
                 di ketiga warna itu. --}}
            <div class="mb-4 inline-flex rounded-lg bg-white px-3 py-2">
                <x-application-logo class="h-7 w-auto" />
            </div>
            <p class="text-lg font-bold leading-tight">SiMoncer</p>
            <p class="mt-1 text-xs leading-snug text-ui-card/70">Sistem Monitoring Pencairan</p>
        </div>

        {{-- Identitas akun: nama dan peran disatukan di satu tempat, tidak lagi
             terpecah antara sidebar dan header halaman, dan ukurannya dinaikkan
             agar terbaca sekali lihat. --}}
        @auth
            <div class="border-b border-ui-card/20 px-6 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ui-card/60">Masuk sebagai</p>
                <p class="mt-1.5 text-base font-bold leading-snug">{{ $user?->nama_lengkap ?? $user?->name ?? '-' }}</p>
                <p class="mt-1 text-sm font-medium leading-snug text-ui-card/80">{{ $roleLabel }}</p>
            </div>
        @endauth

        <nav class="space-y-1 p-4 text-sm font-medium">
            @if($role === 'pegawai')
                <a href="{{ route('pegawai.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Dashboard</a>
                <a href="{{ route('pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan</a>
                <a href="{{ route('pengajuan.create') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Buat Pengajuan</a>
            @elseif($role === 'admin')
                <p class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-ui-card/60">Monitoring</p>
                <a href="{{ route('admin.monitoring.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Dashboard Monitoring</a>
                <a href="{{ route('admin.monitoring.pengajuan') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Semua Pengajuan</a>
                <a href="{{ route('admin.monitoring.aktivitas') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Log Aktivitas</a>

                <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-ui-card/60">Administrasi</p>
                <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Manajemen User</a>
                <a href="{{ route('admin.divisi.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Manajemen Divisi</a>
            @elseif(in_array($role, ['verifikator', 'verifikator_1', 'verifikator_2', 'verifikator_3']))
                <a href="{{ route('verifikator.pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan</a>
                <a href="{{ route('verifikator.riwayat') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Riwayat</a>
            @elseif($role === 'bendahara')
                <a href="{{ route('bendahara.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Dashboard</a>
                <a href="{{ route('bendahara.pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan</a>
                <a href="{{ route('bendahara.riwayat') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Riwayat</a>
            @elseif($role === 'ppk')
                <a href="{{ route('ppk.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Dashboard</a>
                <a href="{{ route('ppk.pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan</a>
                <a href="{{ route('ppk.riwayat') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Riwayat</a>
            @elseif($role === 'ppspm')
                <a href="{{ route('ppspm.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Dashboard</a>
                <a href="{{ route('ppspm.pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan</a>
                <a href="{{ route('ppspm.riwayat') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Riwayat</a>
            @endif

            {{-- Pengajuan pribadi: role pemeriksa pun dapat mengajukan SPJ sendiri.
                 Diletakkan di luar percabangan peran karena menu tugas pemeriksaan
                 di atas tetap utuh — ini tambahan, bukan pengganti.
                 Pegawai tidak disertakan (menunya sudah ada di blok perannya) dan
                 admin tidak disertakan (hanya memantau). --}}
            @if(in_array($role, ['verifikator', 'ppk', 'bendahara', 'ppspm']))
                <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-ui-card/60">Pengajuan Saya</p>
                <a href="{{ route('pengajuan.create') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Buat Pengajuan</a>
                <a href="{{ route('pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan Saya</a>
            @endif

            {{-- Menu akun: tersedia untuk semua role, di luar percabangan peran --}}
            @auth
                <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-ui-card/60">Akun</p>
                <a href="{{ route('password.edit') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Ganti Password</a>
            @endauth
        </nav>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="flex items-center justify-between border-b border-ui-border bg-ui-card px-4 py-4 md:px-8">
            {{-- Peran tidak lagi ditulis di sini; sudah menyatu dengan nama
                 akun pada blok identitas di sidebar. --}}
            <h1 class="text-xl font-semibold">{{ $title }}</h1>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium hover:bg-ui-page">
                        Logout
                    </button>
                </form>
            @endauth
        </header>

        <main class="p-4 md:p-8">
            @if(session('success'))
                <div class="mb-5 rounded-lg border border-status-approved/20 bg-status-approved/10 px-4 py-3 text-sm text-status-approved">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 rounded-lg border border-status-rejected/20 bg-status-rejected/10 px-4 py-3 text-sm text-status-rejected">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Pesan validasi form; tanpa blok ini kegagalan validasi hanya
                 memuat ulang halaman tanpa penjelasan apa pun.
                 $errors hanya di-share oleh middleware group "web", karena itu
                 keberadaannya diperiksa agar layout tetap aman dirender di luar request. --}}
            @if(isset($errors) && $errors->any())
                <div class="mb-5 rounded-lg border border-status-rejected/20 bg-status-rejected/10 px-4 py-3 text-sm text-status-rejected">
                    <p class="font-semibold">Periksa kembali isian berikut:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiKeputusan(event, formElement) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Keputusan',
                text: "Apakah Anda yakin ingin memproses data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Proses',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    formElement.submit();
                }
            });
        }
    </script>
</body>
</html>