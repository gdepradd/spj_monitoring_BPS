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
                        <!-- Inisialisasi state Alpine.js di sini agar sinkron -->
                        <div class="space-y-4" x-data="{ keputusan: '', metode: '', caraBayar: '' }">
                            
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
                            <div x-show="keputusan == '1'" class="bg-blue-50 p-4 rounded border border-blue-100 mt-4" style="display: none;">
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

                            <!-- FORM INPUT SPP (MUNCUL JIKA METODE LS DIPILIH) -->
                            <template x-if="keputusan == '1' && (metode == 'LS_BENDAHARA' || metode == 'LS_PIHAK_KETIGA')">
                                <div class="bg-yellow-50 p-4 rounded border border-yellow-200 mt-4 shadow-sm">
                                    <h4 class="font-bold text-sm text-gray-800 mb-3 border-b border-yellow-300 pb-1">Informasi Penerbitan SPP</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">No. SPP</label>
                                            <input type="text" name="no_spp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tanggal SPP</label>
                                            <input type="date" name="tgl_spp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- FORM PEMBAYARAN LANGSUNG (MUNCUL JIKA METODE UP/TUP DIPILIH) -->
                            <template x-if="keputusan == '1' && metode == 'UP_TUP'">
                                <div class="bg-green-50 p-4 rounded border border-green-200 mt-4 shadow-sm">
                                    <h4 class="font-bold text-sm text-gray-800 mb-3 border-b border-green-300 pb-1">Bukti Pembayaran Langsung</h4>
                                    <p class="mb-3 text-xs text-gray-600">Metode UP/TUP selesai di tahap ini, sehingga bukti pembayarannya dicatat sekarang.</p>

                                    <!-- Pilih cara bayar: transfer atau tunai -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Cara Pembayaran:</label>
                                        <div class="flex flex-wrap gap-4">
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="cara_bayar" value="TRANSFER" x-model="caraBayar" :required="metode == 'UP_TUP'" class="text-green-600">
                                                <span class="text-sm font-medium">Transfer</span>
                                            </label>
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="cara_bayar" value="TUNAI" x-model="caraBayar" :required="metode == 'UP_TUP'" class="text-green-600">
                                                <span class="text-sm font-medium">Tunai</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Tanggal pembayaran: label menyesuaikan cara bayar -->
                                    <div class="mb-4" x-show="caraBayar !== ''" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700">
                                            <span x-show="caraBayar === 'TUNAI'" style="display: none;">Tanggal Dibayarkan</span>
                                            <span x-show="caraBayar !== 'TUNAI'">Tanggal Transfer</span>
                                        </label>
                                        <input type="date" name="tgl_transfer" :required="caraBayar !== ''"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>

                                    <!-- Jalur TRANSFER -->
                                    <template x-if="caraBayar === 'TRANSFER'">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nama Bank</label>
                                                <input type="text" name="nama_bank" placeholder="Contoh: BRI / BNI / Mandiri" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                                                <input type="text" name="no_rekening" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Atas Nama Rekening</label>
                                                <input type="text" name="atas_nama_rekening" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Jalur TUNAI -->
                                    <template x-if="caraBayar === 'TUNAI'">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nomor Kwitansi</label>
                                            <input type="text" name="no_kwitansi" placeholder="Nomor kwitansi pembayaran tunai" required
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                            <p class="mt-1 text-xs text-gray-600">Pembayaran tunai tidak memerlukan data rekening.</p>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="mt-4">
                                <label class="block font-medium text-sm text-gray-700">Catatan <span x-show="keputusan == '2' || keputusan == '3'" class="text-red-500" style="display:none;">*wajib</span></label>
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
                         <!-- Field untuk LS Pihak Ketiga & Bendahara -->
                            @if(in_array($pengajuan->metode_pembayaran, ['LS_BENDAHARA', 'LS_PIHAK_KETIGA']))
                                @php 
                                    $dataPpspm = $pengajuan->ppspm->sortByDesc('id_ppspm')->first(); 
                                    $dataSpp = $pengajuan->bendahara
                                        ->where('tahap', 'PENGAJUAN_SPP')
                                        ->sortByDesc('id_bendahara')
                                        ->first();
                                    $dataPpk = $pengajuan->ppk->sortByDesc('id_ppk')->first();
                                @endphp
                                
                                <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 shadow-sm">
                                    <h4 class="font-bold mb-3 border-b border-blue-200 pb-2">Informasi Dokumen Pencairan</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        
                                        <!-- Data SPP -->
                                        <div>
                                            <p class="text-xs font-semibold text-blue-700 uppercase">No. SPP (Bendahara)</p>
                                            <p class="font-medium text-base">{{ $dataSpp?->no_spp ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-blue-700 uppercase">Tanggal SPP</p>
                                            <p class="font-medium text-base">{{ $dataSpp?->tgl_spp ? \Carbon\Carbon::parse($dataSpp->tgl_spp)->format('d/m/Y') : '-' }}</p>
                                        </div>

                                        <!-- Data Persetujuan PPK -->
                                        <div>
                                            <p class="text-xs font-semibold text-blue-700 uppercase">Disetujui PPK</p>
                                            <p class="font-medium text-base">{{ $dataPpk?->user->nama_lengkap ?? $dataPpk?->user->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-blue-700 uppercase">Tanggal Persetujuan PPK</p>
                                            <p class="font-medium text-base">{{ $dataPpk?->tanggal_proses ? \Carbon\Carbon::parse($dataPpk->tanggal_proses)->format('d/m/Y H:i') : '-' }}</p>
                                        </div>
                                        
                                        <!-- Data SPM dari PPSPM (SP2D diisi di form bawah) -->
                                        @if($dataPpspm)
                                            <div>
                                                <p class="text-xs font-semibold text-blue-700 uppercase">No. SPM (PPSPM)</p>
                                                <p class="font-medium text-base">{{ $dataPpspm->no_spm ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-blue-700 uppercase">Tanggal SPM</p>
                                                <p class="font-medium text-base">{{ $dataPpspm->tgl_spm ? \Carbon\Carbon::parse($dataPpspm->tgl_spm)->format('d/m/Y') : '-' }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- SP2D terbit setelah SPM cair, jadi nomornya dicatat di tahap ini -->
                            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm">
                                <h4 class="font-bold text-sm text-gray-800 mb-3 border-b border-green-300 pb-1">Penerbitan SP2D</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="no_sp2d" class="block text-sm font-medium text-gray-700">Nomor SP2D</label>
                                        <input type="text" id="no_sp2d" name="no_sp2d" value="{{ old('no_sp2d') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        @error('no_sp2d')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="tgl_sp2d" class="block text-sm font-medium text-gray-700">Tanggal SP2D</label>
                                        <input type="date" id="tgl_sp2d" name="tgl_sp2d" value="{{ old('tgl_sp2d') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        @error('tgl_sp2d')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            @if($pengajuan->metode_pembayaran === 'LS_PIHAK_KETIGA')
                                {{-- Kemenkeu mentransfer langsung ke pihak ketiga, sehingga Bendahara
                                     hanya mencatat kapan dananya cair. Tidak ada pilihan cara bayar
                                     maupun data rekening di jalur ini. --}}
                                <div class="mb-4">
                                    <label for="tgl_transfer" class="block font-medium text-sm text-gray-700">Tanggal Cair dari Kemenkeu</label>
                                    <input type="date" id="tgl_transfer" name="tgl_transfer" value="{{ old('tgl_transfer') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('tgl_transfer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    <p class="mt-1 text-xs text-gray-600">
                                        Dana ditransfer langsung oleh Kemenkeu ke pihak ketiga, jadi data rekening tidak diisi di sini.
                                    </p>
                                </div>
                            @else
                                {{-- LS Bendahara: dana masuk ke Bendahara lalu diteruskan ke penerima,
                                     bisa lewat transfer maupun tunai. --}}
                                <div x-data="{ caraBayar: '{{ old('cara_bayar', '') }}' }">

                                    <div class="mb-4">
                                        <label class="block font-medium text-sm text-gray-700 mb-2">Cara Pembayaran ke Penerima:</label>
                                        <div class="flex flex-wrap gap-4">
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="cara_bayar" value="TRANSFER" x-model="caraBayar" required class="text-blue-600">
                                                <span class="text-sm font-medium">Transfer</span>
                                            </label>
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="cara_bayar" value="TUNAI" x-model="caraBayar" required class="text-blue-600">
                                                <span class="text-sm font-medium">Tunai</span>
                                            </label>
                                        </div>
                                        @error('cara_bayar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <!-- Tanggal pembayaran: label menyesuaikan cara bayar -->
                                    <div class="mb-4" x-show="caraBayar !== ''" style="display: none;">
                                        <label for="tgl_transfer" class="block font-medium text-sm text-gray-700">
                                            <span x-show="caraBayar === 'TUNAI'" style="display: none;">Tanggal Pencairan</span>
                                            <span x-show="caraBayar !== 'TUNAI'">Tanggal Transfer</span>
                                        </label>
                                        <input type="date" id="tgl_transfer" name="tgl_transfer" value="{{ old('tgl_transfer') }}" :required="caraBayar !== ''"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('tgl_transfer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <!-- Jalur TRANSFER -->
                                    <template x-if="caraBayar === 'TRANSFER'">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nama Bank</label>
                                                <input type="text" name="nama_bank" value="{{ old('nama_bank') }}" placeholder="Contoh: BRI / BNI / Mandiri" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                                                <input type="text" name="no_rekening" value="{{ old('no_rekening') }}" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Atas Nama Rekening</label>
                                                <input type="text" name="atas_nama_rekening" value="{{ old('atas_nama_rekening') }}" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Jalur TUNAI -->
                                    <template x-if="caraBayar === 'TUNAI'">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700">Nomor Kwitansi</label>
                                            <input type="text" name="no_kwitansi" value="{{ old('no_kwitansi') }}" placeholder="Nomor kwitansi pembayaran tunai" required
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-600">Pembayaran tunai tidak memerlukan data rekening.</p>
                                        </div>
                                    </template>
                                </div>
                            @endif

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
                <div class="mb-4 flex items-center justify-between border-b pb-2">
                    <h3 class="font-bold text-lg">Linimasa Pengajuan</h3>
                    <a href="{{ route('riwayat.detail', $pengajuan->id_pengajuan) }}" class="rounded-lg border border-blue-300 px-3 py-1 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                        Lihat Detail Lengkap
                    </a>
                </div>
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