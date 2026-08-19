<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Pengajuan: ' . $pengajuan->no_pengajuan) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Detail Data Pengajuan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Informasi Pengajuan</h3>
                <p><strong>Pengaju:</strong> {{ $pengajuan->user->name ?? '-' }}</p>
                <p><strong>Nominal:</strong> Rp {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}</p>
                <p><strong>Catatan Pengaju:</strong> {{ $pengajuan->catatan_pengaju ?? '-' }}</p>
            </div>
            <div class="mt-6 bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Linimasa Pengajuan</h3>
                <div class="border-l-2 border-gray-200 ml-4 space-y-6">
                    @foreach ($timeline as $t)
                        <div class="relative pl-6">
                            <div class="absolute -left-1.5 top-1.5 w-3 h-3 bg-blue-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-gray-900">{{ $t['judul'] }} oleh
                                {{ $t['aktor'] ?? 'Sistem' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($t['waktu'])->format('d M Y H:i') }}</p>
                            <p class="text-sm text-gray-700 mt-1">Status: <strong>{{ $t['nama_status'] }}</strong></p>
                            @if (!empty($t['catatan']))
                                <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded mt-1">Catatan:
                                    {{ $t['catatan'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Form Keputusan (Alpine.js state) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="{ keputusan: '' }">
                <h3 class="font-bold text-lg mb-4">Form Keputusan Verifikasi</h3>

                <form action="{{ route('verifikator.pengajuan.keputusan', $pengajuan->id_pengajuan) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Pilih Keputusan:</label>
                        <!-- Ganti value 1, 2, 3 dengan ID dari tabel status_verifikasi sesuai seeder -->
                        <div class="mt-2 space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="id_status_verifikasi" value="1" x-model="keputusan"
                                    class="text-green-600" required>
                                <span class="ml-2">ACC</span>
                            </label>
                            <label class="inline-flex items-center ml-4">
                                <input type="radio" name="id_status_verifikasi" value="2" x-model="keputusan"
                                    class="text-orange-600" required>
                                <span class="ml-2">REVISI</span>
                            </label>
                            <label class="inline-flex items-center ml-4">
                                <input type="radio" name="id_status_verifikasi" value="3" x-model="keputusan"
                                    class="text-red-600" required>
                                <span class="ml-2">TOLAK</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="catatan" class="block font-medium text-sm text-gray-700">Catatan <span
                                x-show="keputusan == '2' || keputusan == '3'" class="text-red-500">*</span></label>
                        <textarea name="catatan" id="catatan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm"
                            :required="keputusan == '2' || keputusan == '3'"></textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                            class="rounded-lg bg-green-600 px-6 py-3 font-semibold text-white shadow
               hover:bg-green-700 transition">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
