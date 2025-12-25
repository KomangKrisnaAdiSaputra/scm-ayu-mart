<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

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
        ];

        // ✅ UNIQUE kode_produk (exclude produk_id saat edit)
        $rules['kode_produk'] .= $id
            ? "|unique:produk,kode_produk,{$id},produk_id"
            : "|unique:produk,kode_produk";

        $validated = $request->validate($rules);

        // ✅ UPDATE or CREATE berdasarkan produk_id
        Produk::updateOrCreate(
            ['produk_id' => $id],
            $validated
        );

        return redirect()->route('produk')
            ->with(
                'success',
                $id
                    ? 'Produk berhasil diperbarui'
                    : 'Produk berhasil ditambahkan'
            );
    }

    public function delete($id)
    {
        Produk::where('produk_id', $id)->delete();

        return redirect()->route('produk')->with('success', 'Produk berhasil dihapus');
    }
}
