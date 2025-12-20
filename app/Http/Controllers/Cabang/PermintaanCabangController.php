<?php

namespace App\Http\Controllers\Cabang;

use App\Http\Controllers\Controller;
use App\Models\PermintaanCabang;
use App\Models\DetailPermintaanCabang;
use Illuminate\Http\Request;

class PermintaanCabangController extends Controller
{
    public function index()
    {
        return view('cabang.permintaan.index', [
            'permintaan' => PermintaanCabang::where('cabang_id', auth()->user()->users_id)->get()
        ]);
    }

    public function store(Request $request)
    {
        $permintaan = PermintaanCabang::create([
            'cabang_id' => auth()->user()->users_id,
            'tanggal_permintaan' => now(),
            'status_permintaan' => 'Menunggu'
        ]);

        foreach ($request->produk as $item) {
            DetailPermintaanCabang::create([
                'permintaan_id' => $permintaan->permintaan_id,
                'produk_id' => $item['produk_id'],
                'qty_permintaan' => $item['qty']
            ]);
        }

        return back()->with('success','Permintaan restok dikirim');
    }
}
