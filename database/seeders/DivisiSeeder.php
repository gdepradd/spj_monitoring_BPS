<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    /**
     * Divisi awal mengikuti fungsi di BPS kabupaten. Daftar ini hanya data awal —
     * admin dapat menambah, mengubah, atau menghapusnya dari menu Manajemen Divisi.
     */
    public function run(): void
    {
        $divisi = [
            ['nama_divisi' => 'Umum', 'keterangan' => 'Subbagian Umum dan Tata Usaha'],
            ['nama_divisi' => 'Produksi', 'keterangan' => 'Fungsi Statistik Produksi'],
            ['nama_divisi' => 'Distribusi', 'keterangan' => 'Fungsi Statistik Distribusi'],
            ['nama_divisi' => 'Sosial', 'keterangan' => 'Fungsi Statistik Sosial'],
            ['nama_divisi' => 'Nerwilis', 'keterangan' => 'Neraca Wilayah dan Analisis Statistik'],
            ['nama_divisi' => 'IPDS', 'keterangan' => 'Integrasi Pengolahan dan Diseminasi Statistik'],
        ];

        foreach ($divisi as $item) {
            Divisi::updateOrCreate(['nama_divisi' => $item['nama_divisi']], $item);
        }
    }
}
