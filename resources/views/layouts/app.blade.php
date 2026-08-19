@props(['title' => 'SPJ Monitoring'])

@php
    $user = auth()->user();
    $role = $user?->role?->nama_role;

    $sidebarClass = match ($role) {
        'pegawai' => 'bg-pov-pengajuan',
        'verifikator' => 'bg-pov-verifikasi',
        'ppk', 'bendahara', 'ppspm' => 'bg-pov-pencairan',
        default => 'bg-status-neutral',
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - SPJ Monitoring BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ui-page text-ui-text antialiased">
<div class="min-h-screen md:flex">
    <aside class="{{ $sidebarClass }} w-full text-ui-card md:min-h-screen md:w-64">
        <div class="border-b border-ui-card/20 px-6 py-5">
            <p class="text-lg font-bold">SPJ Monitoring BPS</p>
            <p class="mt-1 text-xs text-ui-card/80">{{ $user?->nama_lengkap }}</p>
        </div>

        <nav class="space-y-1 p-4 text-sm font-medium">
            @if($role === 'pegawai')
                <a href="{{ route('pegawai.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Dashboard</a>
                <a href="{{ route('pegawai.pengajuan.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Daftar Pengajuan</a>
                <a href="{{ route('pegawai.pengajuan.create') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Buat Pengajuan</a>
                <a href="{{ route('pegawai.pengajuan.riwayat') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Riwayat Pengajuan</a>
            @elseif($role === 'admin')
                <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ui-card/10">Manajemen User</a>
            @else
                <p class="rounded-lg px-3 py-2 text-ui-card/80">Menu modul {{ ucfirst((string) $role) }} akan dilengkapi Dev 2.</p>
            @endif
        </nav>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="flex items-center justify-between border-b border-ui-border bg-ui-card px-4 py-4 md:px-8">
            <div>
                <h1 class="text-lg font-semibold">{{ $title }}</h1>
                <p class="text-xs text-ui-muted">Role: {{ ucfirst((string) $role) }}</p>
            </div>

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

            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
