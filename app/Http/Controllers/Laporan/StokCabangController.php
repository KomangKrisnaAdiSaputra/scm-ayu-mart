<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbCabang;
use App\Models\Integrasi\TbProduk;
use App\Models\Integrasi\TbStokCabang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StokCabangController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------
        | SEARCH PRODUK
        |--------------------------------------
        */
        $produkIds = [];

        if ($request->filled('search')) {
            $produkIds = TbProduk::where('kode_produk', 'like', "%{$request->search}%")
                ->orWhere('nama_produk', 'like', "%{$request->search}%")
                ->pluck('id_produk')
                ->toArray();
        }

        /*
        |--------------------------------------
        | QUERY STOK CABANG
        |--------------------------------------
        */
        $stokCabang = TbStokCabang::with('cabang')
            ->when($request->filled('search'), function ($q) use ($produkIds) {
                $q->whereIn('id_produk', $produkIds);
            })
            ->when($request->filled('id_cabang'), function ($q) use ($request) {
                $q->where('id_cabang', $request->id_cabang);
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'menipis') {
                    $q->whereColumn('total_stok', '<=', 'stok_minimum');
                }
                if ($request->status === 'aman') {
                    $q->whereColumn('total_stok', '>', 'stok_minimum');
                }
            })
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------
        | DATA MASTER
        |--------------------------------------
        */
        $cabangs = TbCabang::orderBy('nama_cabang')->get();

        $produk = TbProduk::whereIn(
            'id_produk',
            $stokCabang->pluck('id_produk')->unique()
        )->get()->keyBy('id_produk');

        return view('laporan.stok_cabang.index', compact(
            'stokCabang',
            'produk',
            'cabangs'
        ));
    }

    public function exportPdf(Request $request)
    {
        $produkIds = [];

        if ($request->filled('search')) {
            $produkIds = TbProduk::where('kode_produk', 'like', "%{$request->search}%")
                ->orWhere('nama_produk', 'like', "%{$request->search}%")
                ->pluck('id_produk')
                ->toArray();
        }

        $stokCabang = TbStokCabang::with('cabang')
            ->when($request->filled('search'), function ($q) use ($produkIds) {
                $q->whereIn('id_produk', $produkIds);
            })
            ->when($request->filled('id_cabang'), function ($q) use ($request) {
                $q->where('id_cabang', $request->id_cabang);
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'menipis') {
                    $q->whereColumn('total_stok', '<=', 'stok_minimum');
                }
                if ($request->status === 'aman') {
                    $q->whereColumn('total_stok', '>', 'stok_minimum');
                }
            })
            ->get();

        $produk = TbProduk::whereIn(
            'id_produk',
            $stokCabang->pluck('id_produk')->unique()
        )->get()->keyBy('id_produk');

        $pdf = Pdf::loadView(
            'laporan.stok_cabang.pdf',
            compact('stokCabang', 'produk')
        )->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-cabang.pdf');
    }
}
