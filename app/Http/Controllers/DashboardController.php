<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbProduk;
use App\Models\StokGudang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function index()
    {
        $stokGudang = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')->limit(5)->get();
        $produkMenipis = TbProduk::whereIn("id_produk", $stokGudang->pluck("produk_id"))->get();

        return view("dashboard", compact("produkMenipis"));
    }
}
