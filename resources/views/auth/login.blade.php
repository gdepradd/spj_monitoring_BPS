<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SPJ Monitoring BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ui-page p-4 text-ui-text">
    <div class="w-full max-w-md rounded-2xl border border-ui-border bg-ui-card p-8 shadow-sm">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold">SPJ Monitoring BPS</h1>
            <p class="mt-2 text-sm text-ui-muted">Masuk menggunakan akun yang diberikan administrator.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-status-rejected/20 bg-status-rejected/10 p-3 text-sm text-status-rejected">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            </div>

            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" required autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-between">
                @if(Route::has('password.request'))
                    <a class="text-sm text-pov-pengajuan hover:underline" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
                <x-primary-button>Masuk</x-primary-button>
            </div>
        </form>
    </div>
</body>
</html>
