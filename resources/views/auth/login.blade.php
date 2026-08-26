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
            <x-application-logo class="mx-auto h-auto w-60" />
            <h1 class="mt-6 text-2xl font-bold">SPJ Monitoring BPS</h1>
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

            <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <label for="password" class="block font-medium text-sm text-gray-700">
                Password
            </label>
            <div class="relative mt-1">
                <input id="password" 
                       class="block w-full pr-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                       :type="show ? 'text' : 'password'"
                       name="password"
                       required autocomplete="current-password" />
                       
                <button type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none">
                    
                    <!-- Icon Eye (Tampil jika show = false) -->
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>

                    <!-- Icon Eye-Slash (Tampil jika show = true) -->
                    <svg x-show="show" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

            <div class="flex items-center justify-between">
                <!-- @if(Route::has('password.request'))
                    <a class="text-sm text-pov-pengajuan hover:underline" href="{{ route('password.request') }}">Lupa password?</a>
                @endif -->
                <x-primary-button>Masuk</x-primary-button>
            </div>
        </form>
    </div>
</body>
</html>
