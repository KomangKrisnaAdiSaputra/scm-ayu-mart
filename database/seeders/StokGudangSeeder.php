<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StokGudang;

class StokGudangSeeder extends Seeder
{
    public function run()
    {
        StokGudang::create([
            'produk_id' => 1,
            'stok_total' => 50,
            'stok_minimum' => 10
        ]);
    }
}
