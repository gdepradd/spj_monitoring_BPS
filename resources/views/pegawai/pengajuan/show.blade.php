<x-app-layout title="Detail Pengajuan">

    @php
        /*
        |--------------------------------------------------------------------------
        | DATA VERIFIKASI
        |--------------------------------------------------------------------------
        */

        $verifikasi1 = $pengajuan->verifikasi
            ->where('tahap', 1)
            ->sortByDesc('id_verifikasi')
            ->first();

        $verifikasi2 = $pengajuan->verifikasi
            ->where('tahap', 2)
            ->sortByDesc('id_verifikasi')
            ->first();

        $verifikasi3 = $pengajuan->verifikasi
            ->where('tahap', 3)
            ->sortByDesc('id_verifikasi')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | DATA PENCAIRAN
        |--------------------------------------------------------------------------
        */

        $bendaharaSpp = $pengajuan->bendahara
            ->where('tahap', 'PENGAJUAN_SPP')
            ->sortByDesc('id_bendahara')
            ->first();

        $bendaharaLangsung = $pengajuan->bendahara
            ->where('tahap', 'PEMBAYARAN_LANGSUNG')
            ->sortByDesc('id_bendahara')
            ->first();

        $bendaharaKonfirmasi = $pengajuan->bendahara
            ->where('tahap', 'KONFIRMASI')
            ->sortByDesc('id_bendahara')
            ->first();

        $ppk = $pengajuan->ppk
            ->sortByDesc('id_ppk')
            ->first();

        $ppspm = $pengajuan->ppspm
            ->sortByDesc('id_ppspm')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | LABEL METODE PEMBAYARAN
        |--------------------------------------------------------------------------
        */

        $metodeLabel = match ($pengajuan->metode_pembayaran) {
            'LS_BENDAHARA' => 'LS Bendahara',
            'LS_PIHAK_KETIGA' => 'LS Pihak Ketiga',
            'UP_TUP' => 'UP / TUP',
            default => 'Belum Ditentukan',
        };
    @endphp


    <div class="grid gap-6 xl:grid-cols-3">

        {{-- ========================================================= --}}
        {{-- INFORMASI PENGAJUAN --}}
        {{-- ========================================================= --}}
        <section class="space-y-6 xl:col-span-2">

            <div class="rounded-xl border border-ui-border bg-ui-card p-6">

                <div class="flex flex-wrap items-start justify-between gap-4">

                    <div>
                        <p class="text-sm text-ui-muted">
                            Nomor Pengajuan
                        </p>

                        <h2 class="mt-1 text-xl font-bold">
                            {{ $pengajuan->no_pengajuan }}
                        </h2>
                    </div>


                    {{-- Status global pengajuan --}}
                    <span
                        @class([
                            'inline-flex rounded-full border px-3 py-1 text-xs font-semibold',

                            'border-amber-200 bg-amber-100 text-amber-700' =>
                                in_array($pengajuan->status?->kode_status, [
                                    'DIAJUKAN',
                                    'VERIFIKASI_1',
                                    'VERIFIKASI_2',
                                    'VERIFIKASI_3',
                                    'MENUNGGU_PENCAIRAN',
                                    'PROSES_PPK',
                                    'PROSES_PPSPM',
                                    'PROSES_KONFIRMASI_BENDAHARA',
                                ]),

                            'border-orange-200 bg-orange-100 text-orange-700' =>
                                $pengajuan->status?->kode_status === 'REVISI',

                            'border-red-200 bg-red-100 text-red-700' =>
                                $pengajuan->status?->kode_status === 'DITOLAK',

                            'border-green-200 bg-green-100 text-green-700' =>
                                $pengajuan->status?->kode_status === 'SELESAI',
                        ])
                    >
                        {{ $pengajuan->status?->nama_status ?? '-' }}
                    </span>

                </div>


                {{-- Informasi utama --}}
                <dl class="mt-6 grid gap-5 sm:grid-cols-2">

                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">
                            Tanggal
                        </dt>

                        <dd class="mt-1">
                            {{ \Carbon\Carbon::parse(
                                $pengajuan->tanggal_pengajuan
                            )->format('d M Y') }}
                        </dd>
                    </div>


                    <div>
                        <dt class="text-xs font-medium uppercase text-ui-muted">
                            Nominal
                        </dt>

                        <dd class="mt-1 font-semibold">
                            Rp {{ number_format(
                                (float) $pengajuan->total_nominal,
                                2,
                                ',',
                                '.'
                            ) }}
                        </dd>
                    </div>


                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase text-ui-muted">
                            Perihal
                        </dt>

                        <dd class="mt-1">
                            {{ $pengajuan->perihal }}
                        </dd>
                    </div>


                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase text-ui-muted">
                            Keterangan
                        </dt>

                        <dd class="mt-1 whitespace-pre-line">
                            {{ $pengajuan->keterangan ?? '-' }}
                        </dd>
                    </div>


                    @if ($pengajuan->catatan_pengaju)
                        <div class="sm:col-span-2">

                            <dt class="text-xs font-medium uppercase text-ui-muted">
                                Catatan Pengaju
                            </dt>

                            <dd class="mt-1 whitespace-pre-line">
                                {{ $pengajuan->catatan_pengaju }}
                            </dd>

                        </div>
                    @endif

                </dl>


                @if ($pengajuan->status?->kode_status === 'REVISI')

                    <a
                        href="{{ route(
                            'pegawai.pengajuan.edit',
                            $pengajuan
                        ) }}"
                        class="mt-6 inline-flex rounded-lg
                               bg-status-revisi px-4 py-2
                               text-sm font-semibold text-ui-card"
                    >
                        Perbaiki Pengajuan
                    </a>

                @endif

            </div>


            {{-- ========================================================= --}}
            {{-- INFORMASI PENCAIRAN --}}
            {{-- ========================================================= --}}
            <div class="rounded-xl border border-ui-border bg-ui-card p-6">

                <div class="flex flex-wrap items-center justify-between gap-4">

                    <div>
                        <h3 class="font-semibold">
                            Informasi Pencairan
                        </h3>

                        <p class="mt-1 text-sm text-ui-muted">
                            Informasi metode pembayaran yang dipilih Bendahara.
                        </p>
                    </div>


                    @if ($pengajuan->metode_pembayaran)

                        <span
                            class="inline-flex rounded-full
                                   border border-purple-200
                                   bg-purple-50 px-3 py-1
                                   text-xs font-semibold
                                   text-purple-700"
                        >
                            {{ $metodeLabel }}
                        </span>

                    @else

                        <span
                            class="inline-flex rounded-full
                                   border border-gray-200
                                   bg-gray-100 px-3 py-1
                                   text-xs font-semibold
                                   text-gray-500"
                        >
                            Belum Ditentukan
                        </span>

                    @endif

                </div>


                <div class="mt-5">

                    @if (!$pengajuan->metode_pembayaran)

                        <div
                            class="rounded-lg border border-dashed
                                   border-gray-300 bg-gray-50 p-4"
                        >
                            <p class="text-sm text-ui-muted">

                                @if (
                                    $pengajuan->isTahapAktif(
                                        'MENUNGGU_PENCAIRAN'
                                    )
                                )
                                    Pengajuan sudah lolos verifikasi dan
                                    menunggu Bendahara menentukan metode
                                    pembayaran.
                                @else
                                    Metode pembayaran belum ditentukan.
                                @endif

                            </p>
                        </div>

                    @else

                        <dl class="grid gap-5 sm:grid-cols-2">

                            <div>
                                <dt
                                    class="text-xs font-medium
                                           uppercase text-ui-muted"
                                >
                                    Metode Pembayaran
                                </dt>

                                <dd class="mt-1 font-semibold text-purple-700">
                                    {{ $metodeLabel }}
                                </dd>
                            </div>


                            <div>
                                <dt
                                    class="text-xs font-medium
                                           uppercase text-ui-muted"
                                >
                                    Alur Pencairan
                                </dt>

                                <dd class="mt-1 text-sm">

                                    @if (
                                        $pengajuan->metode_pembayaran
                                        === 'UP_TUP'
                                    )

                                        Bendahara → Pembayaran Langsung

                                    @else

                                        Bendahara → PPK → PPSPM →
                                        Konfirmasi Bendahara

                                    @endif

                                </dd>
                            </div>

                        </dl>

                    @endif

                </div>

            </div>

        </section>



        {{-- ============================================================= --}}
        {{-- TIMELINE --}}
        {{-- ============================================================= --}}
        <aside>

            <div
                class="rounded-xl border border-ui-border
                       bg-ui-card p-6"
            >

                <div>
                    <h3 class="font-semibold">
                        Timeline Status
                    </h3>

                    <p class="mt-1 text-xs text-ui-muted">
                        Riwayat proses pengajuan dari verifikasi
                        hingga pencairan.
                    </p>
                </div>


                <div class="mt-6">

                    {{-- ================================================= --}}
                    {{-- PENGAJUAN --}}
                    {{-- ================================================= --}}
                    <x-timeline-item
                        label="Pengajuan Dibuat"
                        status="SELESAI"
                        :aktif="false"
                        :waktu="$pengajuan->created_at"
                        :catatan="$pengajuan->catatan_pengaju"
                    />


                    {{-- ================================================= --}}
                    {{-- VERIFIKATOR 1 --}}
                    {{-- ================================================= --}}
                    <x-timeline-item
                        :label="
                            'Verifikator 1' .
                            (
                                $verifikasi1?->verifikator
                                    ? ' (' .
                                      $verifikasi1->verifikator->nama_lengkap .
                                      ')'
                                    : ''
                            )
                        "
                        :status="$verifikasi1?->statusVerifikasi"
                        :aktif="
                            $pengajuan->isTahapAktif(
                                'VERIFIKASI_1'
                            )
                        "
                        :waktu="$verifikasi1?->tanggal_verifikasi"
                        :catatan="$verifikasi1?->catatan"
                    />


                    {{-- ================================================= --}}
                    {{-- VERIFIKATOR 2 --}}
                    {{-- ================================================= --}}
                    <x-timeline-item
                        :label="
                            'Verifikator 2' .
                            (
                                $verifikasi2?->verifikator
                                    ? ' (' .
                                      $verifikasi2->verifikator->nama_lengkap .
                                      ')'
                                    : ''
                            )
                        "
                        :status="$verifikasi2?->statusVerifikasi"
                        :aktif="
                            $pengajuan->isTahapAktif(
                                'VERIFIKASI_2'
                            )
                        "
                        :waktu="$verifikasi2?->tanggal_verifikasi"
                        :catatan="$verifikasi2?->catatan"
                    />


                    {{-- ================================================= --}}
                    {{-- VERIFIKATOR 3 --}}
                    {{-- ================================================= --}}
                    <x-timeline-item
                        :label="
                            'Verifikator 3' .
                            (
                                $verifikasi3?->verifikator
                                    ? ' (' .
                                      $verifikasi3->verifikator->nama_lengkap .
                                      ')'
                                    : ''
                            )
                        "
                        :status="$verifikasi3?->statusVerifikasi"
                        :aktif="
                            $pengajuan->isTahapAktif(
                                'VERIFIKASI_3'
                            )
                        "
                        :waktu="$verifikasi3?->tanggal_verifikasi"
                        :catatan="$verifikasi3?->catatan"
                    />


                    {{-- ================================================= --}}
                    {{-- PENCAIRAN BELUM DIPILIH --}}
                    {{-- ================================================= --}}
                    @if (!$pengajuan->metode_pembayaran)

                        <x-timeline-item
                            label="Bendahara — Pemilihan Metode Pembayaran"
                            :status="null"
                            :aktif="
                                $pengajuan->isTahapAktif(
                                    'MENUNGGU_PENCAIRAN'
                                )
                            "
                            :waktu="null"
                            :catatan="null"
                        />

                    @endif



                    {{-- ================================================= --}}
                    {{-- LS BENDAHARA / LS PIHAK KETIGA --}}
                    {{-- ================================================= --}}
                    @if (
                        in_array(
                            $pengajuan->metode_pembayaran,
                            [
                                'LS_BENDAHARA',
                                'LS_PIHAK_KETIGA',
                            ]
                        )
                    )

                        {{-- Pengajuan SPP --}}
                        <x-timeline-item
                            label="Bendahara — Pengajuan SPP"
                            :status="
                                $bendaharaSpp?->statusPencairan
                            "
                            :aktif="
                                $pengajuan->isTahapAktif(
                                    'MENUNGGU_PENCAIRAN'
                                ) &&
                                !$bendaharaSpp
                            "
                            :waktu="
                                $bendaharaSpp?->tgl_spp
                                ?? $bendaharaSpp?->created_at
                            "
                            :catatan="$bendaharaSpp?->catatan"
                        />


                        {{-- PPK --}}
                        <x-timeline-item
                            label="PPK — Penerbitan SPM"
                            :status="$ppk?->statusPencairan"
                            :aktif="
                                $pengajuan->isTahapAktif(
                                    'PROSES_PPK'
                                )
                            "
                            :waktu="
                                $ppk?->tgl_spm
                                ?? $ppk?->created_at
                            "
                            :catatan="$ppk?->catatan"
                        />


                        {{-- PPSPM --}}
                        <x-timeline-item
                            label="PPSPM — Pengajuan ke Kemenkeu"
                            :status="$ppspm?->statusPencairan"
                            :aktif="
                                $pengajuan->isTahapAktif(
                                    'PROSES_PPSPM'
                                )
                            "
                            :waktu="
                                $ppspm?->tgl_ajukan_kemenkeu
                                ?? $ppspm?->created_at
                            "
                            :catatan="$ppspm?->catatan"
                        />


                        {{-- Konfirmasi Bendahara --}}
                        <x-timeline-item
                            label="Bendahara — Konfirmasi Pencairan"
                            :status="
                                $bendaharaKonfirmasi?->statusPencairan
                            "
                            :aktif="
                                $pengajuan->isTahapAktif(
                                    'PROSES_KONFIRMASI_BENDAHARA'
                                )
                            "
                            :waktu="
                                $bendaharaKonfirmasi?->tgl_transfer
                                ?? $bendaharaKonfirmasi?->created_at
                            "
                            :catatan="
                                $bendaharaKonfirmasi?->catatan
                            "
                        />

                    @endif



                    {{-- ================================================= --}}
                    {{-- UP / TUP --}}
                    {{-- ================================================= --}}
                    @if (
                        $pengajuan->metode_pembayaran === 'UP_TUP'
                    )

                        <x-timeline-item
                            label="Bendahara — Pembayaran Langsung"
                            :status="
                                $bendaharaLangsung?->statusPencairan
                            "
                            :aktif="
                                $pengajuan->isTahapAktif(
                                    'MENUNGGU_PENCAIRAN'
                                )
                            "
                            :waktu="
                                $bendaharaLangsung?->tgl_transfer
                                ?? $bendaharaLangsung?->created_at
                            "
                            :catatan="
                                $bendaharaLangsung?->catatan
                            "
                        />

                    @endif


                    {{-- ================================================= --}}
                    {{-- SELESAI --}}
                    {{-- ================================================= --}}
                    <x-timeline-item
                        label="Pencairan Selesai"
                        :status="
                            $pengajuan->status?->kode_status
                                === 'SELESAI'
                                    ? 'SELESAI'
                                    : null
                        "
                        :aktif="
                            $pengajuan->isTahapAktif(
                                'SELESAI'
                            )
                        "
                        :waktu="
                            $pengajuan->status?->kode_status
                                === 'SELESAI'
                                    ? $pengajuan->updated_at
                                    : null
                        "
                        :catatan="null"
                    />

                </div>

            </div>

        </aside>

    </div>

</x-app-layout>