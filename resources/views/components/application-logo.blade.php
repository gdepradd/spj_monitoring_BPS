{{-- Logo instansi, rasio sangat lanskap (1114x206).

     Kontrak pemakaian: tentukan SATU dimensi saja (mis. class="h-8 w-auto" atau
     class="h-auto w-60"), biarkan sisi lainnya "auto". Komponen ini sengaja tidak
     memberi default w-*/h-* apa pun karena $attributes->merge() menggabungkan
     class alih-alih menimpanya, sehingga default ukuran akan bertabrakan dengan
     ukuran yang diberikan pemanggil.

     max-w-full menjaga logo tidak melebar keluar wadahnya di layar sempit, dan
     object-contain memastikan logo mengecil proporsional, bukan gepeng, saat
     batas itu tercapai. --}}
<img
    src="{{ asset('aset/logo-bps.webp') }}"
    alt="Badan Pusat Statistik Kabupaten Batang Hari"
    width="1114"
    height="206"
    {{ $attributes->merge(['class' => 'max-w-full object-contain']) }}
>
