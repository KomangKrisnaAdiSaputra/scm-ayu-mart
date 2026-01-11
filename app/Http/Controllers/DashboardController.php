<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbProduk;
use App\Models\Integrasi\TbStokCabang;
use App\Models\StokGudang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function index()
    {
        if (auth()->user()->role === "Gudang") {
            $ids = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')->limit(5)->get()->pluck("produk_id")->toArray();
        } elseif (auth()->user()->role === "Cabang") {
            $ids = TbStokCabang::whereColumn('total_stok', '<=', 'stok_minimum')->limit(5)->get()->pluck("id_produk")->toArray();
        }

        $produkMenipis = $ids ? TbProduk::whereIn("id_produk", $ids)->get() : collect([]);

        return view("dashboard", compact("produkMenipis"));
    }
}
