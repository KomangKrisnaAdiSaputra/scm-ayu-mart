<?php

namespace App\Http\Controllers;

use App\Models\Integrasi\TbJenis;
use App\Models\Integrasi\TbProduk;
use Illuminate\Http\Request;

class JenisProdukController extends Controller
{
    public function index(Request $request)
    {
        $jenisProduk = TbJenis::when($request->search, function ($q) use ($request) {
            $q->where('nama_jenis', 'like', "%{$request->search}%")
                ->orWhere('deskripsi_jenis', 'like', "%{$request->search}%");
        })
            ->latest()
            ->get();

        return view('jenis_produk.index', compact('jenisProduk'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'deskripsi_jenis'  => 'nullable|string',
        ]);

        TbJenis::updateOrCreate(
            ['id_jenis' => $request->id], // JIKA ADA ID → UPDATE
            [
                'nama_jenis' => $request->nama_jenis,
                'deskripsi_jenis'  => $request->deskripsi_jenis,
            ]
        );

        return back()->with('success', 'Data jenis produk berhasil disimpan');
    }
}
