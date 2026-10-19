
<x-app-layout title="Ajukan Pembayaran">
    <div class="max-w-3xl rounded-xl border border-ui-border bg-ui-card p-6">
        <h2 class="text-xl font-bold">Form Pengajuan Pembayaran</h2>
        <div class="mt-4 rounded-lg border border-status-pending/20 bg-status-pending/10 p-4 text-sm text-ui-text">
            <strong>Catatan:</strong> versi aplikasi ini tidak menggunakan upload berkas. Tuliskan ringkasan dokumen SPJ pada kolom keterangan.
        </div>
        
        <form method="POST" action="{{ route('pengajuan.store') }}" class="mt-6 space-y-5">
            
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Tanggal Pengajuan</label>
                <input type="text" value="{{ \Carbon\Carbon::now()->format('d/m/y') }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed" disabled>
                <input type="hidden" name="tanggal_pengajuan" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            {{-- Tim pengaju. Satu orang bisa tergabung di beberapa tim, jadi:
                 - satu tim  : ditetapkan otomatis, cukup ditampilkan;
                 - banyak tim: pengaju memilih tim mana yang mengajukan;
                 - tanpa tim : barisnya tidak dirender sama sekali. Peran pemeriksa
                   umumnya memang tanpa tim, dan form ini bukan tempat mengurusnya. --}}
            @if($timPengaju->count() === 1)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tim yang mengajukan SPJ</label>
                    <p class="mt-1 font-semibold text-ui-text">{{ $timPengaju->first()->nama_tim }}</p>
                    <p class="mt-1 text-xs text-ui-muted">Diambil otomatis dari tim akun Anda.</p>
                </div>
            @elseif($timPengaju->count() > 1)
                <div class="mb-4">
                    <label for="id_tim" class="block text-sm font-medium text-gray-700">Tim yang mengajukan SPJ</label>
                    <select id="id_tim" name="id_tim" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— Pilih tim —</option>
                        @foreach($timPengaju as $tim)
                            <option value="{{ $tim->id_tim }}" @selected((string) old('id_tim', '') === (string) $tim->id_tim)>{{ $tim->nama_tim }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-ui-muted">Anda tergabung di beberapa tim. Pilih tim yang mengajukan SPJ ini.</p>
                    @error('id_tim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Tanggal Kegiatan</label>
                <input type="date" name="bulan_kegiatan" value="{{ old('bulan_kegiatan') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                @error('bulan_kegiatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                <input type="text" name="nama_penerima" value="{{ old('nama_penerima') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                @error('nama_penerima') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <!-- <div>
                <x-input-label for="tanggal_pengajuan" value="Tanggal Pengajuan" />
                <x-text-input id="tanggal_pengajuan" name="tanggal_pengajuan" type="date" value="{{ old('tanggal_pengajuan', now()->toDateString()) }}" required />
                @error('tanggal_pengajuan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div> -->

            <!-- <div>
                <x-input-label for="perihal" value="Perihal" />
                <x-text-input id="perihal" name="perihal" value="{{ old('perihal') }}" required />
                @error('perihal')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div> -->

            <div>
                <x-input-label for="keterangan" value="Keterangan / Uraian Pengajuan SPJ (Sesuai SPJ)" />
                <textarea id="keterangan" name="keterangan" rows="5" required class="mt-1 block w-full rounded-lg border-ui-border bg-ui-card focus:border-pov-pengajuan focus:ring-pov-pengajuan">{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="total_nominal" value="Total Nominal (Sesuai SPJ)" />
                <x-input-rupiah id="total_nominal" name="total_nominal" :value="old('total_nominal')" required />
                @error('total_nominal')<p class="mt-1 text-sm text-status-rejected">{{ $message }}</p>@enderror
            </div>

            {{-- Daftar penerima pembayaran. Opsional, dan bisa diimpor dari berkas
                 Excel/CSV supaya daftar yang panjang tidak perlu diketik satu per satu.
                 Bendahara memakai daftar ini sebagai isian awal form pencairan. --}}
            <div>
                <x-penerima-pengajuan />
            </div>

            <div class="flex gap-3">
                <x-primary-button>Kirim Pengajuan</x-primary-button>
                <a href="{{ route('pengajuan.index') }}" class="rounded-lg border border-ui-border px-4 py-2 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("input[name='bulan_kegiatan']", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/y",
            allowInput: true
        });
    </script>
</x-app-layout>
