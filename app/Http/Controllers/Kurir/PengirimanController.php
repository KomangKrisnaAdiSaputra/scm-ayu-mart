<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\StatusKurir;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    public function index()
    {
        return view('kurir.pengiriman.index', [
            'pengiriman' => Pengiriman::all()
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        StatusKurir::create([
            'pengiriman_id' => $id,
            'status_kurir' => $request->status_kurir,
            'waktu_update' => now(),
            'nama_kurir' => auth()->user()->nama
        ]);

        return back()->with('success','Status pengiriman diperbarui');
    }
}
