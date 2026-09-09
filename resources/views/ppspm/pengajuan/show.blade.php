<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Proses PPSPM: ' . $pengajuan->no_pengajuan) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Detail Pengaju -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Informasi Pemohon</h3>
                <div class="space-y-2 text-sm">
                    <p><strong>Nama Lengkap:</strong> {{ $pengajuan->user->nama_lengkap ?? $pengajuan->user->name ?? '-' }}</p>
                    <p class="text-sm text-gray-700"><span class="font-bold">Tim Pengaju:</span> {{ $pengajuan->tim_pengaju ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $pengajuan->user->email ?? '-' }}</p>
                    <p><strong>Tanggal Pengajuan:</strong> {{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</p>
                    <!-- <p><strong>Perihal:</strong> {{ $pengajuan->perihal }}</p> -->
                    <p><strong>Metode Pembayaran:</strong> <span class="bg-gray-200 px-2 py-1 rounded">{{ $pengajuan->metode_pembayaran }}</span></p>
                    <p class="text-sm text-gray-700"><span class="font-bold">Bulan Kegiatan:</span> {{ $pengajuan->bulan_kegiatan ?? '-' }}</p>
                    <p class="text-sm text-gray-700"><span class="font-bold">Nama Penerima:</span> {{ $pengajuan->nama_penerima ?? '-' }}</p>
                    <p class="text-lg font-bold text-indigo-600 mt-2">Total Nominal: Rp {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Area Form Ajukan Kemenkeu -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Pengajuan Kemenkeu
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Teruskan pengajuan pencairan ke Kementerian Keuangan.
                    </p>
                </div>

                <div class="p-6">
                    @php
                        $spp = $pengajuan->bendahara
                            ->where('tahap', 'PENGAJUAN_SPP')
                            ->sortByDesc('id_bendahara')
                            ->first();
                        $dataPpk = $pengajuan->ppk->sortByDesc('id_ppk')->first();
                    @endphp

                    <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
                        <h4 class="font-bold mb-2 border-b border-indigo-300 pb-2">Informasi Dokumen SPP</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <p class="text-xs text-indigo-600 uppercase font-semibold">Nomor SPP</p>
                                <p class="font-medium text-base">{{ $spp?->no_spp ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 uppercase font-semibold">Tanggal SPP</p>
                                <p class="font-medium text-base">{{ $spp?->tgl_spp ? \Carbon\Carbon::parse($spp->tgl_spp)->format('d M Y') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 uppercase font-semibold">Disetujui PPK</p>
                                <p class="font-medium text-base">{{ $dataPpk?->user->nama_lengkap ?? $dataPpk?->user->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 uppercase font-semibold">Tanggal Persetujuan PPK</p>
                                <p class="font-medium text-base">{{ $dataPpk?->tanggal_proses ? \Carbon\Carbon::parse($dataPpk->tanggal_proses)->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($pengajuan->status->kode_status === 'PROSES_PPSPM')
                        <form action="{{ route('ppspm.pengajuan.ajukan-kemenkeu', $pengajuan->id_pengajuan) }}" method="POST" onsubmit="konfirmasiKeputusan(event, this)">
                            @csrf
                            <div class="space-y-5">
                                <!-- Jika Dev 1 sudah migrate kolom tgl_ajukan_kemenkeu, hilangkan komentar blok ini -->
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Tanggal Pengajuan Kemenkeu</label>
                                    <input type="date" name="tgl_ajukan_kemenkeu" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                               
                                {{-- Nomor SPM umumnya hanya berbeda beberapa karakter dari nomor SPP,
                                     jadi kolomnya diisi awal dengan nomor SPP dan tinggal disunting.
                                     Isian tetap bisa dikosongkan bila SPM benar-benar berbeda. --}}
                                <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2"
                                     x-data="{
                                         dasarSpp: @js($spp?->no_spp ?? ''),
                                         noSpm: @js(old('no_spm', $spp?->no_spp ?? '')),
                                     }">
                                    <div>
                                        <label for="no_spm" class="block text-sm font-medium text-gray-700">No. SPM</label>

                                        <input type="text" id="no_spm" name="no_spm" x-model="noSpm" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                        <template x-if="dasarSpp">
                                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                                <span class="text-gray-500">
                                                    Dasar dari SPP: <span class="font-medium text-gray-700" x-text="dasarSpp"></span>
                                                </span>

                                                <button type="button" @click="noSpm = dasarSpp"
                                                        x-show="noSpm !== dasarSpp"
                                                        class="font-semibold text-indigo-600 hover:underline">
                                                    Samakan dengan SPP
                                                </button>

                                                <button type="button" @click="noSpm = ''"
                                                        x-show="noSpm !== ''"
                                                        class="font-semibold text-gray-500 hover:underline">
                                                    Kosongkan
                                                </button>
                                            </div>
                                        </template>

                                        @error('no_spm')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="tgl_spm" class="block text-sm font-medium text-gray-700">Tanggal SPM</label>
                                        <input type="date" id="tgl_spm" name="tgl_spm" value="{{ old('tgl_spm') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('tgl_spm')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Catatan (Opsional)</label>
                                    <textarea name="catatan" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>

                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                                    Ajukan ke Kemenkeu
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="text-gray-500 text-center py-4">Pengajuan ini tidak berada pada tahap PPSPM.</p>
                    @endif
                </div>
            </div>
            <!-- Area Linimasa (Timeline) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                <div class="mb-4 flex items-center justify-between border-b pb-2">
                    <h3 class="font-bold text-lg">Linimasa Pengajuan</h3>
                    <a href="{{ route('riwayat.detail', $pengajuan->id_pengajuan) }}" class="rounded-lg border border-indigo-300 px-3 py-1 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                        Lihat Detail Lengkap
                    </a>
                </div>
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