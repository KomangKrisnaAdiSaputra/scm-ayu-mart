<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        return view('manajer.produk.index', [
            'produk' => Produk::all()
        ]);
    }

    public function store(Request $request)
    {
        Produk::create($request->validate([
            'kode_produk' => 'required',
            'nama_produk' => 'required',
            'kategori' => 'required',
            'satuan' => 'required',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'status_produk' => 'required'
        ]));

        return back()->with('success','Produk berhasil ditambahkan');
    }
}
