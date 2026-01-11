<?php

namespace Database\Seeders;

use App\Models\Integrasi\TbJenis;
use App\Models\Integrasi\TbProduk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run()
    {
        $jenis = TbJenis::create([
            "nama_jenis" => "Sembako",
            "deskripsi_jenis" => "test"
        ]);

        TbProduk::create([
            'id_jenis' => $jenis->id_jenis,
            'kode_produk' => 'BRG001',
            'nama_produk' => 'Gula 1 Kg',
            'deskripsi_produk' => 'Sembako',
            'satuan' => 'Kg',
            'harga_beli' => 12000,
            'harga_produk' => 15000,
            'status_produk' => 'aktif'
        ]);
    }
}
