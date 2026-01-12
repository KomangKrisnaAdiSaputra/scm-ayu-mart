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
        $produkGudang = collect([]);
        $stokGudang = collect([]);
        $produkCabang = collect([]);
        $stokCabang = collect([]);

        if (auth()->user()->role === "Gudang") {
            $stokGudang = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')->limit(5)->get();
            $produkGudang = TbProduk::whereIn("id_produk", $stokGudang->pluck("produk_id")->toArray())->get();
        } elseif (auth()->user()->role === "Cabang") {
            $stokCabang = TbStokCabang::whereColumn('total_stok', '<=', 'stok_minimum')->limit(5)->get();
            $produkGudang = TbProduk::whereIn("id_produk", $stokCabang->pluck("produk_id")->toArray())->get();
        }

        return view("dashboard", compact("produkGudang", "stokGudang", "produkCabang", "stokCabang"));
    }
}
