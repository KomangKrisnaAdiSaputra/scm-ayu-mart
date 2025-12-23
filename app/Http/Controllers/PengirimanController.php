<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\StatusKurir;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $pengiriman = Pengiriman::with([
            'permintaan.detail.produk',
            'permintaan.cabang'
        ])
            ->when($search, function ($query) use ($search) {

                $query->where('pengiriman_id', 'like', "%{$search}%")
                    ->orWhere('status_pengiriman', 'like', "%{$search}%")
                    ->orWhereDate('tanggal_kirim', $search)

                    // search lewat relasi permintaan
                    ->orWhereHas('permintaan', function ($q) use ($search) {
                        $q->where('permintaan_id', 'like', "%{$search}%");
                    })

                    // search nama cabang
                    ->orWhereHas('permintaan.cabang', function ($q) use ($search) {
                        $q->where('nama_cabang', 'like', "%{$search}%");
                    });
            })
            ->orderBy('tanggal_kirim', 'desc')
            ->get();

        return view('pengiriman.index', compact('pengiriman', 'search'));
    }


    public function updateStatus(Request $request, $id)
    {
        StatusKurir::create([
            'pengiriman_id' => $id,
            'status_kurir' => $request->status_kurir,
            'waktu_update' => now(),
            'nama_kurir' => auth()->user()->nama
        ]);

        return back()->with('success', 'Status pengiriman diperbarui');
    }
}
