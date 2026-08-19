<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - SPJ Monitoring BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ui-page p-4 text-ui-text">
    <div class="max-w-lg rounded-2xl border border-ui-border bg-ui-card p-8 text-center">
        <p class="text-5xl font-bold text-status-neutral">404</p>
        <h1 class="mt-4 text-2xl font-bold">Halaman Tidak Ditemukan</h1>
        <p class="mt-2 text-ui-muted">Halaman yang Anda cari tidak tersedia.</p>
        <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-lg bg-status-neutral px-4 py-2 text-sm font-semibold text-ui-card">Kembali</a>
    </div>
</body>
</html>
