<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    public function run()
    {
        Produk::create([
            'kode_produk' => 'BRG001',
            'nama_produk' => 'Gula 1 Kg',
            'kategori' => 'Sembako',
            'satuan' => 'Kg',
            'harga_beli' => 12000,
            'harga_jual' => 15000,
            'status_produk' => 'aktif'
        ]);
    }
}
