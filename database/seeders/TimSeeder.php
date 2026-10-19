<?php

namespace Database\Seeders;

use App\Models\Tim;
use Illuminate\Database\Seeder;

class TimSeeder extends Seeder
{
    /**
     * Tim awal mengikuti fungsi di BPS kabupaten. Daftar ini hanya data awal —
     * admin dapat menambah, mengubah, atau menghapusnya dari menu Manajemen Tim.
     */
    public function run(): void
    {
        $tim = [
            ['nama_tim' => 'Umum', 'keterangan' => 'Subbagian Umum dan Tata Usaha'],
            ['nama_tim' => 'Produksi', 'keterangan' => 'Fungsi Statistik Produksi'],
            ['nama_tim' => 'Distribusi', 'keterangan' => 'Fungsi Statistik Distribusi'],
            ['nama_tim' => 'Sosial', 'keterangan' => 'Fungsi Statistik Sosial'],
            ['nama_tim' => 'Nerwilis', 'keterangan' => 'Neraca Wilayah dan Analisis Statistik'],
            ['nama_tim' => 'IPDS', 'keterangan' => 'Integrasi Pengolahan dan Diseminasi Statistik'],
        ];

        foreach ($tim as $item) {
            Tim::updateOrCreate(['nama_tim' => $item['nama_tim']], $item);
        }
    }
}
