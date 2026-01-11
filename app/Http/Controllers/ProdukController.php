<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbCabang;
use App\Models\Integrasi\TbJenis;
use App\Models\Integrasi\TbProduk;
use App\Models\Integrasi\TbStokCabang;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $cabang = TbCabang::where('users_id', auth()->user()->users_id)->first();
        $search = $request->query('search');

        $produk = TbProduk::with(["jenis", "stok_cabangs" => function ($q) use ($cabang) {
            if ($cabang) $q->where("id_cabang", $cabang->id_cabang);
        }, "stok_cabangs.cabang"])->when($search, function ($query, $search) {
            $query->where('kode_produk', 'like', "%{$search}%")
                ->orWhere('nama_produk', 'like', "%{$search}%");
        })
            // ->whereHas("stok_cabangs", function ($q) use ($cabang) {
            //     if ($cabang) $q->where("id_cabang", $cabang->id_cabang);
            // })
            ->get();

        $stok = StokGudang::select(
            'produk_id',
            'stok_total',
            'stok_minimum'
        )->get()->keyBy('produk_id');
        return view('produk.index', compact('produk', 'search', 'stok'));
    }

    public function form($id = null)
    {
        $produk = $id ? TbProduk::findOrFail($id) : null;
        $jenis = TbJenis::all();
        return view('produk.form', compact('produk', 'jenis'));
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'kode_produk'   => 'required|string|max:255',
            'nama_produk'   => 'required|string|max:255',
            'kategori'      => 'required|integer|exists:mysqlIntegration.tb_jenis,id_jenis',
            'satuan'        => 'required|string|max:255',
            'harga_beli'    => 'required|numeric|min:0',
            'harga_jual'    => 'required|numeric|min:0',
            'deskripsi_produk' => 'nullable|string',
            'berat_produk'  => 'nullable|string|max:50',
            'foto_produk'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status_produk' => 'required|in:aktif,nonaktif',
            'stok_minimum'  => 'required|integer|min:0',

            'is_diskon_active' => 'nullable|boolean',
            'harga_diskon' => 'nullable|numeric|min:0',
            'tanggal_mulai_diskon' => 'nullable|date',
            'tanggal_akhir_diskon' => 'nullable|date|after_or_equal:tanggal_mulai_diskon',
        ];

        if (!$id) {
            $rules['stok_total'] = 'required|integer|min:0';
        }

        $rules['kode_produk'] .= $id
            ? "|unique:mysqlIntegration.tb_produk,kode_produk,{$id},id_produk"
            : "|unique:mysqlIntegration.tb_produk,kode_produk";

        $validated = $request->validate($rules);

        DB::transaction(function () use ($request, $validated, $id) {

            // Upload foto
            if ($request->hasFile('foto_produk')) {
                $validated['foto_produk'] = $request->file('foto_produk')->store('produk', 'public');
                $validated['foto_produk'] = basename($validated['foto_produk']);
            }

            $produk = TbProduk::updateOrCreate(
                ['id_produk' => $id],
                [
                    'id_jenis' => $validated['kategori'],
                    'kode_produk' => $validated['kode_produk'],
                    'nama_produk' => $validated['nama_produk'],
                    'deskripsi_produk' => $validated['deskripsi_produk'] ?? null,
                    'harga_beli' => $validated['harga_beli'],
                    'harga_produk' => $validated['harga_jual'],
                    'is_diskon_active' => $validated['is_diskon_active'] ?? 0,
                    'harga_diskon' => $validated['harga_diskon'] ?? null,
                    'tanggal_mulai_diskon' => $validated['tanggal_mulai_diskon'] ?? null,
                    'tanggal_akhir_diskon' => $validated['tanggal_akhir_diskon'] ?? null,
                    'berat_produk' => $validated['berat_produk'] ?? null,
                    'foto_produk' => $validated['foto_produk'] ?? ($produk->foto_produk ?? null),
                    'status_produk' => $validated['status_produk'],
                    'satuan' => $validated['satuan'],
                ]
            );

            if (!$id) {
                StokGudang::create([
                    'produk_id' => $produk->id_produk,
                    'stok_total' => $validated['stok_total'],
                    'stok_minimum' => $validated['stok_minimum'],
                ]);
            } else {
                StokGudang::where('produk_id', $produk->id_produk)
                    ->update([
                        'stok_minimum' => $validated['stok_minimum'],
                    ]);
            }
        });

        return redirect()->route('produk')
            ->with('success', $id ? 'Produk berhasil diperbarui' : 'Produk berhasil ditambahkan');
    }


    public function delete($id)
    {
        TbProduk::where('id_produk', $id)->delete();

        return redirect()->route('produk')->with('success', 'Produk berhasil dihapus');
    }
}
