<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Integrasi\TbCabang;
use App\Models\Integrasi\TbProduk;
use App\Models\Integrasi\TbStokCabang;
use App\Models\Pengiriman;
use App\Models\StatusKurir;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $allCabang = TbCabang::all();
        $allProduk = TbProduk::all();
        $cabang = TbCabang::where('nama_cabang', 'like', "%{$search}%")->first();

        $query = Pengiriman::with([
            'permintaan.detail',
            'permintaan',
            'status_kurir'
        ])
            ->when($search, function ($query) use ($search, $cabang) {

                $query->where('pengiriman_id', 'like', "%{$search}%")
                    ->orWhere('status_pengiriman', 'like', "%{$search}%")
                    ->orWhereDate('tanggal_kirim', $search)

                    // search lewat relasi permintaan
                    ->orWhereHas('permintaan', function ($q) use ($search) {
                        $q->where('permintaan_id', 'like', "%{$search}%");
                    })->orWhereHas('permintaan', fn($q) => $q->where('cabang_id', $cabang->cabang_id));

                // // search nama cabang
                // ->orWhereHas('permintaan.cabang', function ($q) use ($search) {
                //     $q->where('nama_cabang', 'like', "%{$search}%");
                // });
            })->orderBy('tanggal_kirim', 'desc');

        if (auth()->user()->role === 'Cabang') {
            $cabangUser = $allCabang->where('users_id', auth()->user()->users_id)->first();
            $query->whereHas('permintaan', function ($q) use ($cabangUser) {
                $q->where('cabang_id', $cabangUser?->id_cabang);
            });
        }
        $pengiriman = $query->get();

        return view('pengiriman.index', compact('pengiriman', 'search', 'allCabang', 'allProduk'));
    }

    public function ambil(Request $request)
    {
        $request->validate([
            'pengiriman_id' => 'required',
            'nama_kurir' => 'required'
        ]);

        DB::transaction(function () use ($request) {

            // update status pengiriman
            $pengiriman = Pengiriman::lockForUpdate()
                ->findOrFail($request->pengiriman_id);

            $pengiriman->update([
                'status_pengiriman' => 'Dikirim'
            ]);

            // insert status kurir
            StatusKurir::create([
                'pengiriman_id' => $pengiriman->pengiriman_id,
                'nama_kurir' => $request->nama_kurir,
                'status_kurir' => 'Dalam Pengiriman',
                'catatan' => $request->catatan,
                'waktu_update' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Pengiriman berhasil diambil');
    }

    public function gagal(Request $request)
    {
        $request->validate([
            'pengiriman_id' => 'required|exists:pengiriman,pengiriman_id',
            'catatan' => 'required|string'
        ]);

        DB::transaction(function () use ($request) {

            // Ambil status kurir
            $statusKurir = StatusKurir::where('pengiriman_id', $request->pengiriman_id)
                ->lockForUpdate()
                ->first();

            if (!$statusKurir) {
                abort(400, 'Status kurir tidak ditemukan.');
            }

            if ($statusKurir->status_kurir === 'Gagal') {
                abort(400, 'Pengiriman sudah berstatus Gagal.');
            }

            // Ambil pengiriman + detail permintaan
            $pengiriman = Pengiriman::with('permintaan.detail')
                ->lockForUpdate()
                ->findOrFail($request->pengiriman_id);

            // 🔄 KEMBALIKAN STOK GUDANG
            foreach ($pengiriman->permintaan->detail as $item) {

                $stokGudang = StokGudang::where('produk_id', $item->produk_id)
                    ->lockForUpdate()
                    ->first();

                if ($stokGudang) {
                    // stok kembali ke gudang
                    $stokGudang->increment('stok_total', $item->qty_permintaan);
                }
            }

            // Update status kurir
            $statusKurir->update([
                'status_kurir'  => 'Gagal',
                'waktu_update' => now(),
                'catatan'      => $request->catatan,
            ]);
        });

        return redirect()->back()->with('success', 'Pengiriman ditandai Gagal dan stok gudang berhasil dikembalikan');
    }

    public function diterima(Request $request)
    {
        $request->validate([
            'pengiriman_id' => 'required|exists:pengiriman,pengiriman_id',
        ]);

        DB::transaction(function () use ($request) {

            // Ambil pengiriman
            $pengiriman = Pengiriman::lockForUpdate()
                ->findOrFail($request->pengiriman_id);

            if ($pengiriman->status_pengiriman !== 'Dikirim') {
                abort(400, 'Pengiriman belum dalam status Dikirim.');
            }

            foreach ($pengiriman?->permintaan?->detail ?? [] as $value) {
                $stokCabang = TbStokCabang::where("id_produk", $value->produk_id)->where("id_cabang", $value->permintaan->cabang_id)->first();

                if ($stokCabang) {
                    TbStokCabang::find($stokCabang->id_stok_cabang)->increment('total_stok', $value->qty_permintaan);
                } else {
                    TbStokCabang::create([
                        "id_produk" => $value->produk_id,
                        "id_cabang" => $value->permintaan->cabang_id,
                        "total_stok" => $value->qty_permintaan,
                        "stok_minimum" => $value->qty_permintaan
                    ]);
                }
            }

            // Update status pengiriman
            $pengiriman->update([
                'status_pengiriman' => 'Diterima'
            ]);

            // Update status kurir
            StatusKurir::where('pengiriman_id', $pengiriman->pengiriman_id)
                ->lockForUpdate()
                ->update([
                    'status_kurir'  => 'Terkirim',
                    'waktu_update' => now(),
                ]);
        });

        return redirect()->back()->with('success', 'Pengiriman berhasil dikonfirmasi diterima');
    }
}
