<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    function index()
    {
        $produkMenipis = Produk::with('stok')
            ->whereHas('stok', function ($q) {
                $q->whereColumn('stok_total', '<=', 'stok_minimum');
            })
            ->limit(5)
            ->get();

        return view("dashboard", compact("produkMenipis"));
    }
}
