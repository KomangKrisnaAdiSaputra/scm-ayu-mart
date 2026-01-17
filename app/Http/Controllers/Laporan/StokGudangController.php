<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbProduk;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StokGudangController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------
        | SEARCH PRODUK (DB PRODUK)
        |--------------------------------------
        */
        $produkIds = [];

        if ($request->filled('search')) {
            $produkIds = TbProduk::where('kode_produk', 'like', '%' . $request->search . '%')
                ->orWhere('nama_produk', 'like', '%' . $request->search . '%')
                ->pluck('id_produk')
                ->toArray();
        }

        /*
        |--------------------------------------
        | QUERY STOK GUDANG
        |--------------------------------------
        */
        $stok = StokGudang::query()
            ->when($request->filled('search'), function ($q) use ($produkIds) {
                $q->whereIn('produk_id', $produkIds);
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'menipis') {
                    $q->whereColumn('stok_total', '<=', 'stok_minimum');
                }
                if ($request->status === 'aman') {
                    $q->whereColumn('stok_total', '>', 'stok_minimum');
                }
            })
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------
        | AMBIL DATA PRODUK
        |--------------------------------------
        */
        $produk = TbProduk::whereIn(
            'id_produk',
            $stok->pluck('produk_id')->unique()
        )->get()->keyBy('id_produk');


        return view('laporan.stok_gudang.index', compact('stok', 'produk'));
    }

    public function exportPdf(Request $request)
    {
        $produkIds = [];

        if ($request->filled('search')) {
            $produkIds = TbProduk::where('kode_produk', 'like', '%' . $request->search . '%')
                ->orWhere('nama_produk', 'like', '%' . $request->search . '%')
                ->pluck('id_produk')
                ->toArray();
        }

        $stok = StokGudang::query()
            ->when($request->filled('search'), function ($q) use ($produkIds) {
                $q->whereIn('produk_id', $produkIds);
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'menipis') {
                    $q->whereColumn('stok_total', '<=', 'stok_minimum');
                }
                if ($request->status === 'aman') {
                    $q->whereColumn('stok_total', '>', 'stok_minimum');
                }
            })
            ->get();

        $produk = TbProduk::whereIn(
            'id_produk',
            $stok->pluck('produk_id')->unique()
        )->get()->keyBy('id_produk');

        $pdf = Pdf::loadView('laporan.stok_gudang.pdf', compact('stok', 'produk'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-gudang.pdf');
    }
}
