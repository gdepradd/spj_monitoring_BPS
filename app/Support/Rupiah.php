<?php

namespace App\Support;

class Rupiah
{
    /**
     * Ubah angka rupiah berpemisah ribuan menjadi angka murni.
     *
     * Kolom uang di aplikasi ini ditampilkan berformat "200.000" oleh komponen
     * x-input-rupiah, sementara nilai yang dikirim ke server berasal dari input
     * hidden berisi angka murni. Fungsi ini menjadi jaring kedua: bila nilai
     * berformat tetap sampai ke server — misalnya JavaScript gagal dimuat atau
     * form dikirim dari luar aplikasi — "200.000" akan dibaca aturan validasi
     * 'numeric' sebagai 200 dan tersimpan sebagai dua ratus rupiah tanpa
     * peringatan apa pun. Kesalahan senilai seribu kali itu tidak boleh lewat
     * diam-diam pada aplikasi pencairan anggaran.
     *
     * Pembersihan dibuat sesempit mungkin: hanya string yang seluruhnya
     * berpola kelompok ribuan yang diubah. Dengan begitu "200.5" (dua ratus
     * rupiah lima puluh sen) tetap utuh, demikian pula nilai yang bukan string.
     */
    public static function keAngka(mixed $nilai): mixed
    {
        if (! is_string($nilai)) {
            return $nilai;
        }

        $bersih = trim($nilai);

        // Contoh yang cocok: "200.000", "1.234.567". Tidak cocok: "200.5",
        // "200000", "1.23", "abc".
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $bersih) === 1) {
            return str_replace('.', '', $bersih);
        }

        return $nilai;
    }
}
