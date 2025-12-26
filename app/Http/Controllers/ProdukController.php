<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $produk = Produk::with(['stok'])->when($search, function ($query, $search) {
            $query->where('kode_produk', 'like', "%{$search}%")
                ->orWhere('nama_produk', 'like', "%{$search}%");
        })->get();
        return view('produk.index', compact('produk', 'search'));
    }

    public function form($id = null)
    {
        $produk = $id ? Produk::findOrFail($id) : null;
        return view('produk.form', compact('produk'));
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'kode_produk'   => 'required|string|max:50',
            'nama_produk'   => 'required|string|max:100',
            'kategori'      => 'required|string|max:50',
            'satuan'        => 'required|string|max:30',
            'harga_beli'    => 'required|numeric|min:0',
            'harga_jual'    => 'required|numeric|min:0',
            'status_produk' => 'required|in:aktif,nonaktif',
            'stok_minimum'  => 'required|integer|min:0',
        ];

        if (!$id) {
            $rules['stok_total'] = 'required|integer|min:0';
        }

        $rules['kode_produk'] .= $id
            ? "|unique:produk,kode_produk,{$id},produk_id"
            : "|unique:produk,kode_produk";

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $id) {

            $produk = Produk::updateOrCreate(
                ['produk_id' => $id],
                collect($validated)->except(['stok_total', 'stok_minimum'])->toArray()
            );

            if (!$id) {
                StokGudang::create([
                    'produk_id'    => $produk->produk_id,
                    'stok_total'   => $validated['stok_total'],
                    'stok_minimum' => $validated['stok_minimum'],
                ]);
            } else {
                StokGudang::where('produk_id', $produk->produk_id)
                    ->update([
                        'stok_minimum' => $validated['stok_minimum']
                    ]);
            }
        });

        return redirect()->route('produk')->with(
            'success',
            $id ? 'Produk berhasil diperbarui' : 'Produk berhasil ditambahkan'
        );
    }

    public function delete($id)
    {
        Produk::where('produk_id', $id)->delete();

        return redirect()->route('produk')->with('success', 'Produk berhasil dihapus');
    }
}
