<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbCabang;
use App\Models\Integrasi\TbJenis;
use App\Models\Integrasi\TbProduk;
use App\Models\Integrasi\TbStokCabang;
use App\Models\RiwayatStok;
use App\Models\StokGudang;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $userLogin = Auth::user();
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

        $riwayatStok = RiwayatStok::whereIn("produk_id", $produk->pluck("id_produk")->toArray())->where("type", $userLogin->role)->orderByDesc("created_at")->get();
        $stok = StokGudang::select(
            'produk_id',
            'stok_total',
            'stok_minimum'
        )->get()->keyBy('produk_id');
        return view('produk.index', compact('produk', 'search', 'stok', 'riwayatStok'));
    }

    public function form($id = null)
    {
        $produk = $id ? TbProduk::findOrFail($id) : null;
        $stokGudang = StokGudang::where("produk_id", $id)->first();
        $jenis = TbJenis::all();
        return view('produk.form', compact('produk', 'jenis', 'stokGudang'));
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
            $produk = TbProduk::find($id);
            $data = [
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
                'foto_produk' => $produk?->foto_produk ?? null,
                'status_produk' => $validated['status_produk'],
                'satuan' => $validated['satuan'],
            ];

            // Upload foto
            if ($request->hasFile('foto_produk')) {
                if ($produk?->foto_produk) {
                    $imagePublicId = pathinfo(parse_url($produk->foto_produk, PHP_URL_PATH), PATHINFO_FILENAME);
                    Cloudinary::destroy($imagePublicId);
                }

                $upload = Cloudinary::upload($request->file('foto_produk')->getRealPath(), [
                    'folder' => 'produk'
                ]);
                $data["foto_produk"] = $upload->getSecurePath();
            }

            if ($produk) {
                $produk->update($data);
            } else {
                $produk =  TbProduk::create($data);
            }


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
        $produk = TbProduk::where('id_produk', $id);
        if ($produk->foto_produk) {
            $imagePublicId = pathinfo(parse_url($produk->foto_produk, PHP_URL_PATH), PATHINFO_FILENAME);
            Cloudinary::destroy($imagePublicId);
        }
        $produk->delete();

        return redirect()->route('produk')->with('success', 'Produk berhasil dihapus');
    }

    public function saveStokCabang(Request $request, $id)
    {
        $request->validate([
            'stok_minimum' => 'required|integer|min:0'
        ]);

        TbStokCabang::find($id)
            ->update([
                'stok_minimum' => $request->stok_minimum,
                'updated_at' => now()
            ]);

        return back()->with('success', 'Stok minimum berhasil diperbarui');
    }

    public function updateStok(Request $request)
    {
        $request->validate([
            'produk_id' => 'required',
            'stok_baru' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);
        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {

            // =====================
            // ROLE GUDANG
            // =====================
            if ($user->role === 'Gudang') {

                $stok = StokGudang::where('produk_id', $request->produk_id)
                    ->lockForUpdate()
                    ->first();

                $qtyLama = $stok->stok_total ?? 0;

                StokGudang::updateOrInsert(
                    ['produk_id' => $request->produk_id],
                    [
                        'stok_minimum' => ($stok?->stok_minimum ?? 0) > 0 ? $stok->stok_minimum : $request->stok_baru,
                        'stok_total' => $request->stok_baru,
                        'updated_at' => now(),
                    ]
                );

                DB::table('riwayat_stok')->insert([
                    'produk_id' => $request->produk_id,
                    'type' => 'Gudang',
                    'nama' => 'Gudang Utama',
                    'nama_user' => $user->nama,
                    'qty_lama' => $qtyLama,
                    'qty_baru' => $request->stok_baru,
                    'keterangan' => $request->keterangan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // =====================
            // ROLE CABANG
            // =====================
            if ($user->role === 'Cabang') {
                $cabang = TbCabang::where("users_id", $user->users_id)->first();
                $stok = TbStokCabang::where('id_produk', $request->produk_id)
                    ->where('id_cabang', $cabang->id_cabang)
                    ->lockForUpdate()
                    ->first();

                $qtyLama = $stok?->total_stok ?? 0;

                TbStokCabang::updateOrInsert(
                    [
                        'id_produk' => $request->produk_id,
                        'id_cabang' => $cabang->id_cabang
                    ],
                    [
                        'id_produk' => $request->produk_id,
                        'id_cabang' => $cabang->id_cabang,
                        'stok_minimum' => ($stok?->stok_minimum ?? 0) > 0 ? $stok->stok_minimum : $request->stok_baru,
                        'total_stok' => $request->stok_baru,
                        'updated_at' => now(),
                    ]
                );

                RiwayatStok::create([
                    'produk_id' => $request->produk_id,
                    'type' => 'Cabang',
                    'nama' => $cabang->nama_cabang,
                    'nama_user' => $user->nama,
                    'qty_lama' => $qtyLama,
                    'qty_baru' => $request->stok_baru,
                    'keterangan' => $request->keterangan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Stok berhasil diperbarui');
    }
}
