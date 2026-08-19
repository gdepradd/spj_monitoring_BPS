<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Verifikator Tahap ' . $urutan) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>Selamat datang, Verifikator.</p>
                    <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        Anda memiliki <strong>{{ $totalMenunggu }}</strong> pengajuan yang menunggu untuk diverifikasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>