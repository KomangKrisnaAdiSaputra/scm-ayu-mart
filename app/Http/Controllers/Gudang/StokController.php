<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\StokGudang;

class StokController extends Controller
{
    public function index()
    {
        $stok = StokGudang::with('produk')->get();
        return view('gudang.stok.index', compact('stok'));
    }
}
