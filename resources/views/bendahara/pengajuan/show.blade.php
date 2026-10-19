@php
    // Usulan penerima dari pemohon dipakai sebagai isian awal form pencairan.
    // Pada pembayaran lanjutan, nama yang sudah lunas dikeluarkan supaya tidak
    // terbayar dua kali.
    $usulanBelumDibayar = $pengajuan->usulanPenerimaBelumDibayar();

    // Adanya daftar penerima berarti uang ini memang dibagi ke beberapa orang,
    // jadi form dibuka pada mode parsial. Pilihan pengguna (old) tetap menang.
    $jenisAwal = old('jenis_pencairan', $pengajuan->penerima->isNotEmpty() ? 'PARSIAL' : '');
@endphp

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

                {{-- Daftar penerima yang diusulkan pengaju. Daftar yang sama dipakai
                     sebagai isian awal form pencairan di sebelah, jadi Bendahara
                     cukup memeriksa lalu menyetujui. --}}
                <div class="mt-4">
                    <x-tabel-penerima :penerima="$pengajuan->penerima" :total="$pengajuan->total_nominal" ringkas />
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
                        {{-- Pencairan diawali pada PARSIAL bila pemohon sudah mengirim
                             daftar penerima: daftarnya langsung terlihat dan Bendahara
                             tinggal memeriksa, bukan mengetik ulang. --}}
                        <div class="space-y-4" x-data="{ keputusan: '', metode: '', caraBayar: '', jenisPencairan: @js($jenisAwal) }">
                            
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
                                    <p class="mb-3 text-xs text-gray-600">
                                        Metode UP/TUP selesai di tahap ini. Total pengajuan
                                        <strong>Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}</strong>.
                                    </p>

                                    <!-- Penuh atau parsial -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencairan:</label>
                                        <div class="flex flex-wrap gap-4">
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="jenis_pencairan" value="PENUH" x-model="jenisPencairan" :required="metode == 'UP_TUP'" class="text-green-600">
                                                <span class="text-sm font-medium">Penuh (satu penerima)</span>
                                            </label>
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="jenis_pencairan" value="PARSIAL" x-model="jenisPencairan" :required="metode == 'UP_TUP'" class="text-green-600">
                                                <span class="text-sm font-medium">Parsial (bisa beberapa penerima)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Cara bayar berlaku untuk seluruh penerima pada pembayaran ini -->
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

                                    <!-- Tanggal berlaku untuk kedua jenis pencairan -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Tanggal Pembayaran</label>
                                        <input type="date" name="tgl_transfer" value="{{ old('tgl_transfer') }}"
                                               :required="metode == 'UP_TUP'"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>

                                    <!-- Satu nomor kwitansi untuk seluruh pembayaran ini -->
                                    <div class="mb-4" x-show="caraBayar === 'TUNAI'" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700">Nomor Kwitansi</label>
                                        <input type="text" name="no_kwitansi" value="{{ old('no_kwitansi') }}"
                                               placeholder="Berlaku untuk seluruh pembayaran pengajuan ini"
                                               :required="caraBayar === 'TUNAI'"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>

                                    <!-- PENUH: satu penerima -->
                                    <template x-if="jenisPencairan === 'PENUH'">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                                            <input type="text" name="nama_penerima"
                                                   value="{{ old('nama_penerima', $pengajuan->nama_penerima) }}"
                                                   :required="jenisPencairan === 'PENUH'"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        </div>
                                    </template>

                                    <!-- PARSIAL: boleh dibagi ke beberapa penerima sekaligus -->
                                    <template x-if="jenisPencairan === 'PARSIAL'">
                                        <div>
                                        <x-penerima-repeater :batas="(float) $pengajuan->total_nominal"
                                                             :usulan="$pengajuan->penerima" warna="green" />
                                        <p class="mt-2 text-xs text-gray-600">
                                            Sisa yang belum dibayar tetap tampil di dashboard untuk dilunasi menyusul.
                                        </p>
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

                            {{-- Bendahara meneruskan dana ke penerima pada kedua jalur LS,
                                 baik LS Bendahara maupun LS Pihak Ketiga. Karena itu cara bayar
                                 (transfer/tunai) dan pencairan penuh/parsial tersedia di keduanya,
                                 sama seperti UP/TUP. --}}
                                {{-- LS Bendahara: dana masuk ke Bendahara lalu diteruskan ke penerima,
                                     bisa lewat transfer maupun tunai, penuh atau bertahap. --}}
                                <div x-data="{
                                        caraBayar: '{{ old('cara_bayar', '') }}',
                                        jenisPencairan: @js($jenisAwal),
                                     }">

                                    <div class="mb-4">
                                        <label class="block font-medium text-sm text-gray-700 mb-2">Pencairan:</label>
                                        <div class="flex flex-wrap gap-4">
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="jenis_pencairan" value="PENUH" x-model="jenisPencairan" required class="text-blue-600">
                                                <span class="text-sm font-medium">Penuh (satu penerima)</span>
                                            </label>
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" name="jenis_pencairan" value="PARSIAL" x-model="jenisPencairan" required class="text-blue-600">
                                                <span class="text-sm font-medium">Parsial (bisa beberapa penerima)</span>
                                            </label>
                                        </div>
                                        @error('jenis_pencairan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <!-- Cara bayar berlaku untuk seluruh penerima pada pembayaran ini -->
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

                                    <div class="mb-4">
                                        <label for="tgl_transfer" class="block text-sm font-medium text-gray-700">Tanggal Pembayaran</label>
                                        <input type="date" id="tgl_transfer" name="tgl_transfer" value="{{ old('tgl_transfer') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('tgl_transfer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <!-- Satu nomor kwitansi untuk seluruh pembayaran ini -->
                                    <div class="mb-4" x-show="caraBayar === 'TUNAI'" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700">Nomor Kwitansi</label>
                                        <input type="text" name="no_kwitansi" value="{{ old('no_kwitansi') }}"
                                               placeholder="Berlaku untuk seluruh pembayaran pengajuan ini"
                                               :required="caraBayar === 'TUNAI'"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('no_kwitansi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <!-- PENUH: satu penerima -->
                                    <template x-if="jenisPencairan === 'PENUH'">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                                            <input type="text" name="nama_penerima"
                                                   value="{{ old('nama_penerima', $pengajuan->nama_penerima) }}"
                                                   :required="jenisPencairan === 'PENUH'"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('nama_penerima')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                    </template>

                                    <!-- PARSIAL: boleh dibagi ke beberapa penerima sekaligus -->
                                    <template x-if="jenisPencairan === 'PARSIAL'">
                                        <div class="mb-4">
                                        <x-penerima-repeater :batas="(float) $pengajuan->total_nominal"
                                                             :usulan="$pengajuan->penerima" warna="blue" />
                                        <p class="mt-2 text-xs text-gray-600">
                                            Total pengajuan Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}.
                                            Sisanya dibayarkan menyusul dan tetap tampil di dashboard.
                                        </p>
                                    </div>
                                    </template>
                                </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                <textarea name="catatan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm mt-1"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>

                @elseif($pengajuan->status->kode_status === 'PENCAIRAN_PARSIAL')
                    {{-- Pengajuan yang dicairkan bertahap: catat pembayaran berikutnya --}}
                    @php
                        $sisa = $pengajuan->sisa_pembayaran;
                        $dibayar = $pengajuan->total_dibayar;
                    @endphp

                    <h3 class="font-bold text-lg mb-1 border-b pb-2">Pembayaran Berikutnya</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        Metode: <strong>{{ $pengajuan->metode_label }}</strong> ·
                        pencairan <strong>bertahap</strong>
                    </p>

                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase text-amber-700">Total Pengajuan</p>
                                <p class="font-bold">Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-amber-700">Sudah Dibayar</p>
                                <p class="font-bold text-green-700">Rp {{ number_format($dibayar, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-amber-700">Sisa</p>
                                <p class="font-bold text-red-700">Rp {{ number_format($sisa, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-amber-200">
                            <div class="h-full rounded-full bg-green-500" style="width: {{ $pengajuan->persen_dibayar }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-amber-800">{{ $pengajuan->persen_dibayar }}% dari total sudah dibayarkan.</p>
                    </div>

                    <form action="{{ route('bendahara.pengajuan.bayar', $pengajuan->id_pengajuan) }}" method="POST">
                        @csrf
                        <div class="space-y-4" x-data="{
                                caraBayar: '{{ old('cara_bayar', '') }}',
                                jenisPencairan: @js($jenisAwal),
                             }">

                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-2">Jumlah Dibayarkan:</label>
                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="jenis_pencairan" value="PENUH" x-model="jenisPencairan" required class="text-blue-600">
                                        <span class="text-sm font-medium">Lunasi sisa (Rp {{ number_format($sisa, 0, ',', '.') }})</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="jenis_pencairan" value="PARSIAL" x-model="jenisPencairan" required class="text-blue-600">
                                        <span class="text-sm font-medium">Sebagian (bisa beberapa penerima)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Cara bayar berlaku untuk seluruh penerima pada pembayaran ini -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-2">Cara Pembayaran:</label>
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
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Pembayaran</label>
                                <input type="date" name="tgl_transfer" value="{{ old('tgl_transfer') }}" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Satu nomor kwitansi berlaku untuk seluruh pengajuan ini -->
                            @if($pengajuan->no_kwitansi)
                                <div x-show="caraBayar === 'TUNAI'" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700">Nomor Kwitansi</label>
                                    <div class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                                        <span class="font-semibold">{{ $pengajuan->no_kwitansi }}</span>
                                        <span class="text-xs text-gray-600">— mengikuti pembayaran sebelumnya</span>
                                    </div>
                                </div>
                            @else
                                <div x-show="caraBayar === 'TUNAI'" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700">Nomor Kwitansi</label>
                                    <input type="text" name="no_kwitansi" value="{{ old('no_kwitansi') }}"
                                           placeholder="Berlaku untuk seluruh pembayaran pengajuan ini"
                                           :required="caraBayar === 'TUNAI'"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            @endif

                            <!-- PENUH: sisa dilunasi ke satu penerima -->
                            <template x-if="jenisPencairan === 'PENUH'">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                                    <input type="text" name="nama_penerima" value="{{ old('nama_penerima') }}"
                                           :required="jenisPencairan === 'PENUH'"
                                           placeholder="Penerima pelunasan"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </template>

                            <!-- SEBAGIAN: satu pembayaran boleh dibagi ke beberapa penerima -->
                            <template x-if="jenisPencairan === 'PARSIAL'">
                                <div>
                                <x-penerima-repeater :batas="$sisa"
                                                     :usulan="$usulanBelumDibayar" warna="blue" />
                                <p class="mt-2 text-xs text-gray-600">Maksimal Rp {{ number_format($sisa, 0, ',', '.') }}.</p>
                            </div>
                            </template>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                <textarea name="catatan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm mt-1">{{ old('catatan') }}</textarea>
                            </div>

                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">
                                Catat Pembayaran
                            </button>
                        </div>
                    </form>
                @endif

                {{-- Rincian pembayaran yang sudah dicatat --}}
                @php $daftarBayar = $pengajuan->bendahara->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])->sortBy('id_bendahara'); @endphp
                @if($daftarBayar->isNotEmpty())
                    <div class="mt-6 border-t pt-4">
                        <h4 class="font-bold text-sm text-gray-800 mb-3">Pembayaran Tercatat ({{ $daftarBayar->count() }})</h4>
                        <div class="space-y-2">
                            @foreach($daftarBayar as $bayar)
                                <div class="flex flex-wrap items-center justify-between gap-2 rounded border border-gray-200 p-3 text-sm">
                                    <div>
                                        <p class="font-semibold">Rp {{ number_format((float) $bayar->nominal, 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ $bayar->nama_penerima ?? $bayar->atas_nama_rekening ?? '-' }} ·
                                            {{ $bayar->cara_bayar === 'TUNAI' ? 'Tunai' : 'Transfer' }}
                                            @if($bayar->no_kwitansi) · Kwitansi {{ $bayar->no_kwitansi }} @endif
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ $bayar->tgl_transfer ? \Carbon\Carbon::parse($bayar->tgl_transfer)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
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