<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Proses Bendahara: ' . $pengajuan->no_pengajuan) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Detail Pengaju (Sesuai Poin 3 Issue) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Informasi Pemohon</h3>
                <div class="space-y-2 text-sm">
                    <p><strong>Nama Lengkap:</strong> {{ $pengajuan->user->nama_lengkap ?? $pengajuan->user->name ?? '-' }}</p>
                    <p class="text-sm text-gray-700"><span class="font-bold">Tim Pengaju:</span> {{ $pengajuan->tim_pengaju ?? '-' }}</p>
                    <p class="text-sm text-gray-700"><span class="font-bold">Bulan Kegiatan:</span> {{ $pengajuan->bulan_kegiatan ?? '-' }}</p>
                    <p class="text-sm text-gray-700"><span class="font-bold">Nama Penerima:</span> {{ $pengajuan->nama_penerima ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $pengajuan->user->email ?? '-' }}</p>
                    <!-- <p><strong>No. HP:</strong> {{ $pengajuan->user->no_hp ?? '-' }}</p> -->
                    <p><strong>Tanggal Pengajuan:</strong> {{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</p>
                    <!-- <p><strong>Perihal:</strong> {{ $pengajuan->perihal }}</p> -->
                    <p class="text-lg font-bold text-blue-600 mt-2">Total Nominal: Rp {{ number_format($pengajuan->total_nominal, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Area Form Dinamis -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if($pengajuan->status->kode_status === 'MENUNGGU_PENCAIRAN')
                    <!-- FORM TAHAP AWAL (PILIH METODE) -->
                    <!-- FORM TAHAP AWAL (PILIH METODE ATAU TOLAK) -->
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Keputusan & Metode Pembayaran</h3>
                    <form action="{{ route('bendahara.pengajuan.ajukan', $pengajuan->id_pengajuan) }}" method="POST" onsubmit="konfirmasiKeputusan(event, this)">
                        @csrf
                        <div class="space-y-4">
                            
                            <!-- Pilih Keputusan -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-2">Keputusan:</label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="id_status_pencairan" value="1" x-model="keputusan" required class="text-green-600">
                                        <span class="font-semibold text-green-700">ACC & Lanjut</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="id_status_pencairan" value="2" x-model="keputusan" required class="text-orange-600">
                                        <span class="font-semibold text-orange-700">Revisi</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="id_status_pencairan" value="3" x-model="keputusan" required class="text-red-600">
                                        <span class="font-semibold text-red-700">Tolak</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Pilih Metode (Hanya tampil jika ACC) -->
                            <div x-show="keputusan == '1'" class="bg-blue-50 p-4 rounded border border-blue-100 mt-4">
                                <label class="block font-medium text-sm text-blue-900 mb-2">Metode Pembayaran (Pilih Salah Satu):</label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" name="metode_pembayaran" value="LS_BENDAHARA" x-model="metode" :required="keputusan == '1'" class="text-blue-600">
                                    <span>LS Bendahara (Via PPK & PPSPM)</span>
                                </label>
                                <label class="flex items-center space-x-2 mt-2">
                                    <input type="radio" name="metode_pembayaran" value="LS_PIHAK_KETIGA" x-model="metode" :required="keputusan == '1'" class="text-blue-600">
                                    <span>LS Pihak Ketiga (Kemenkeu transfer langsung)</span>
                                </label>
                                <label class="flex items-center space-x-2 mt-2">
                                    <input type="radio" name="metode_pembayaran" value="UP_TUP" x-model="metode" :required="keputusan == '1'" class="text-blue-600">
                                    <span>UP/TUP (Bayar Langsung, Selesai)</span>
                                </label>
                            </div>

                            <div class="mt-4">
                                <label class="block font-medium text-sm text-gray-700">Catatan <span x-show="keputusan == '2' || keputusan == '3'" class="text-red-500">*wajib</span></label>
                                <textarea name="catatan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm mt-1" :required="keputusan == '2' || keputusan == '3'"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4 transition">
    <span x-show="keputusan == '' || keputusan == '1'">
        <span x-show="metode !== 'UP_TUP'">Ajukan Proses</span>
        <span x-show="metode === 'UP_TUP'" style="display: none;">Konfirmasi Bayar & Selesai</span>
    </span>
    <span x-show="keputusan == '2' || keputusan == '3'" style="display: none;">Simpan Keputusan</span>
</button>
                        </div>
                    </form>

                @elseif($pengajuan->status->kode_status === 'PROSES_KONFIRMASI_BENDAHARA')
                    <!-- FORM TAHAP AKHIR (KONFIRMASI) -->
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Konfirmasi Pencairan</h3>
                    <p class="mb-4 text-sm text-gray-600">Metode: <strong>{{ $pengajuan->metode_pembayaran }}</strong></p>
                    
                    <form action="{{ route('bendahara.pengajuan.konfirmasi', $pengajuan->id_pengajuan) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <!-- Field untuk LS Pihak Ketiga -->
                            @if($pengajuan->metode_pembayaran === 'LS_BENDAHARA')
    @php 
        $dataPpspm = $pengajuan->ppspm->sortByDesc('id_ppspm')->first(); 
    @endphp
    @if($dataPpspm)
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            <h4 class="font-bold mb-2">Informasi Dokumen Pencairan (PPSPM)</h4>
            <div class="grid grid-cols-2 gap-2">
                <p><strong>No. SPM:</strong> {{ $dataPpspm->no_spm ?? '-' }}</p>
                <p><strong>Tanggal SPM:</strong> {{ $dataPpspm->tgl_spm ? \Carbon\Carbon::parse($dataPpspm->tgl_spm)->format('d/m/Y') : '-' }}</p>
                <p><strong>No. SP2D:</strong> {{ $dataPpspm->no_sp2d ?? '-' }}</p>
                <p><strong>Tanggal SP2D:</strong> {{ $dataPpspm->tgl_sp2d ? \Carbon\Carbon::parse($dataPpspm->tgl_sp2d)->format('d/m/Y') : '-' }}</p>
            </div>
        </div>
    @endif
@endif

                            <!-- Selalu muncul -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Tanggal Transfer/Konfirmasi</label>
                                <input type="date" name="tgl_transfer" required class="w-full border-gray-300 rounded-md shadow-sm mt-1">
                            </div>
                            <div class="mb-4 mt-4">
    <label class="block text-sm font-medium text-gray-700">Nama Bank</label>
    <input type="text" name="nama_bank" placeholder="Contoh: BRI / BNI / Mandiri" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
    <input type="text" name="no_rekening" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Atas Nama Rekening</label>
    <input type="text" name="atas_nama_rekening" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
</div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                <textarea name="catatan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm mt-1"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Simpan & Selesaikan Pengajuan
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            <!-- Area Linimasa (Timeline) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Linimasa Pengajuan</h3>
                <ol class="relative border-l border-gray-200 ml-3">
                    @foreach($pengajuan->riwayat_lengkap as $item)
    <div class="mb-4 flex items-start space-x-3">
        @php
            $isSelesai = in_array(strtolower($item['status']), ['selesai', 'disetujui', 'acc']);
            $isRevisi = strtolower($item['status']) === 'revisi';
        @endphp
        
        <span class="inline-block h-3 w-3 rounded-full mt-1.5 {{ $isSelesai ? 'bg-green-500' : ($isRevisi ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
        
        <div>
            <p class="font-semibold text-gray-900">{{ $item['judul'] }}</p>
            <p class="text-xs text-gray-500">{{ $item['waktu'] ? \Carbon\Carbon::parse($item['waktu'])->format('d M Y, H:i') : '-' }}</p>
            <p class="text-xs text-gray-700">Aktor: {{ $item['aktor'] }}</p>
            <p class="text-xs font-medium {{ $isSelesai ? 'text-green-600' : ($isRevisi ? 'text-yellow-600' : 'text-red-600') }}">
                Status: {{ $item['status'] }}
            </p>
            @if(!empty($item['catatan']))
                <div class="mt-1 rounded bg-gray-100 p-2 text-xs text-gray-700">
                    <strong>Catatan:</strong> {{ $item['catatan'] }}
                </div>
            @endif
        </div>
    </div>
@endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>