<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Proses PPK: ' . $pengajuan->no_pengajuan) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Detail Pengaju -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Informasi Pemohon</h3>
                <div class="space-y-2 text-sm">
                    <p><strong>Nama Lengkap:</strong> {{ $pengajuan->user->nama_lengkap ?? $pengajuan->user->name ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $pengajuan->user->email ?? '-' }}</p>
                    <!-- <p><strong>No. HP:</strong> {{ $pengajuan->user->no_hp ?? '-' }}</p> -->
                    <p><strong>Tanggal Pengajuan:</strong> {{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</p>
                    <p><strong>Perihal:</strong> {{ $pengajuan->perihal }}</p>
                    <p><strong>Metode Pembayaran:</strong> <span class="bg-gray-200 px-2 py-1 rounded">{{ $pengajuan->metode_pembayaran }}</span></p>
                    <p class="text-lg font-bold text-purple-600 mt-2">Total Nominal: Rp {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Area Form Terbitkan SPM -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Penerbitan SPM
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Terbitkan Surat Perintah Membayar untuk diteruskan ke PPSPM.
                    </p>
                </div>

                <div class="p-6">
                    @if($pengajuan->status->kode_status === 'PROSES_PPK')
                        <form action="{{ route('ppk.pengajuan.terbitkan-spm', $pengajuan->id_pengajuan) }}" method="POST" onsubmit="konfirmasiKeputusan(event, this)">
                            @csrf
                            <div class="space-y-5">
                                <!-- Jika Dev 1 sudah migrate kolom no_spm dan tgl_spm, hilangkan komentar blok ini -->
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">No. SPM</label>
                                    <input type="text" name="no_spm" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Tanggal SPM</label>
                                    <input type="date" name="tgl_spm" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                                </div>
                               

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Catatan (Opsional)</label>
                                    <textarea name="catatan" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"></textarea>
                                </div>

                                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition">
                                    Terbitkan SPM & Teruskan ke PPSPM
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="text-gray-500 text-center py-4">Pengajuan ini tidak berada pada tahap PPK.</p>
                    @endif
                </div>
            </div>
            <!-- Area Linimasa (Timeline) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Linimasa Pengajuan</h3>
                <ol class="relative border-l border-gray-200 ml-3">
                    @foreach($pengajuan->riwayat_lengkap as $riwayat)
                    <li class="mb-8 ml-6">
                        <span class="absolute flex items-center justify-center w-4 h-4 rounded-full -left-2 ring-4 ring-white
                            {{ $riwayat['status'] === 'Selesai' ? 'bg-green-500' : ($riwayat['status'] === 'Sedang Diproses' ? 'bg-blue-500' : ($riwayat['status'] === 'Revisi' || $riwayat['status'] === 'Ditolak' ? 'bg-red-500' : 'bg-gray-300')) }}">
                        </span>
                        <h3 class="flex items-center mb-1 text-md font-semibold text-gray-900">
                            {{ $riwayat['judul'] }} 
                        </h3>
                        <time class="block mb-2 text-sm font-normal leading-none text-gray-400">
                            {{ $riwayat['waktu'] ? \Carbon\Carbon::parse($riwayat['waktu'])->format('d M Y, H:i') : '-' }}
                        </time>
                        <p class="text-sm font-normal text-gray-500">
                            <strong>Aktor:</strong> {{ $riwayat['aktor'] }} <br>
                            <strong>Status:</strong> 
                            <span class="{{ $riwayat['status'] === 'Selesai' ? 'text-green-600' : ($riwayat['status'] === 'Sedang Diproses' ? 'text-blue-600' : 'text-gray-600') }}">
                                {{ $riwayat['status'] }}
                            </span>
                        </p>
                        @if($riwayat['catatan'])
                            <p class="mt-2 text-sm text-gray-700 bg-gray-50 p-2 rounded border border-gray-200">
                                <strong>Catatan:</strong> {{ $riwayat['catatan'] }}
                            </p>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>