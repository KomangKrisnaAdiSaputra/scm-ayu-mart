<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PermintaanCabang;
use App\Models\DetailPermintaanCabang;
use App\Models\Integrasi\TbCabang;
use App\Models\Integrasi\TbProduk;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Models\StokGudang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanCabangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $cabang = TbCabang::where('users_id', auth()->user()->users_id)->first();
        $allCabang = TbCabang::all();
        $allProduk = TbProduk::all();

        $permintaan = PermintaanCabang::with(['detail'])
            ->when(
                !in_array(auth()->user()->role, ['Manajer', 'Gudang']),
                function ($query) use ($cabang) {
                    // 🔒 selain Manajer & gudang → filter cabang
                    $query->where('cabang_id', $cabang?->id_cabang ?? null);
                }
            )
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('status_permintaan', 'like', "%$search%")
                        ->orWhereDate('tanggal_permintaan', $search)
                        ->orWhere('permintaan_id', 'like', "%$search%");
                });
            })
            ->orderBy('tanggal_permintaan', 'desc')
            ->get();

        return view('permintaan_cabang.index', compact('permintaan', 'search', 'cabang', 'allCabang', 'allProduk'));
    }

    function form()
    {
        $stokGudang = StokGudang::where('stok_total', '>', 0)->get();
        return view('permintaan_cabang.form', [
            'produk' => TbProduk::where('status_produk', 'aktif')->whereIn("id_produk", $stokGudang->pluck("produk_id")->toArray())->get()
        ]);
    }

    public function store(Request $request)
    {
        $cabang = TbCabang::where('users_id', auth()->user()->users_id)->first();

        $request->validate([
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:mysqlIntegration.tb_produk,id_produk',
            'produk.*.qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $cabang) {

            // HEADER
            $permintaan = PermintaanCabang::create([
                'cabang_id' => $cabang?->id_cabang ?? null,
                'tanggal_permintaan' => now(),
                'status_permintaan' => 'Menunggu'
            ]);

            $produkGabung = [];
            foreach ($request->produk as $item) {
                $produk_id = (int)$item['id_produk'];

                if (isset($produkGabung[$produk_id])) {
                    // jika produk sudah ada → tambah qty
                    $produkGabung[$produk_id]['qty'] += ((int)$item['qty']);
                } else {
                    // produk baru
                    $produkGabung[$produk_id] = [
                        'produk_id' => $produk_id,
                        'qty' => (int)$item['qty']
                    ];
                }
            }

            // SIMPAN KE DETAIL
            foreach ($produkGabung as $item) {
                DetailPermintaanCabang::create([
                    'permintaan_id' => $permintaan->permintaan_id,
                    'produk_id' => $item['produk_id'],
                    'qty_permintaan' => $item['qty']
                ]);
            }
        });

        return redirect()->route('permintaancabang')
            ->with('success', 'Permintaan berhasil dikirim');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diterima,Ditolak',
        ]);

        // 🔒 hanya gudang
        if (!in_array(auth()->user()->role, ['Gudang'])) {
            abort(403, 'Unauthorized');
        }

        try {
            DB::transaction(function () use ($request, $id) {

                // 🔐 Ambil permintaan + lock
                $permintaan = PermintaanCabang::with('detail')
                    ->where('permintaan_id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ❌ Cegah double proses
                if ($permintaan->status_permintaan !== 'Menunggu') {
                    throw new \Exception('Permintaan sudah diproses sebelumnya');
                }

                // ✅ Jika DITERIMA → kurangi stok gudang
                if ($request->status === 'Diterima') {

                    foreach ($permintaan->detail as $item) {

                        $stok = StokGudang::where('produk_id', $item->produk_id)
                            ->lockForUpdate()
                            ->first();

                        // ❌ stok tidak ada
                        if (!$stok) {
                            throw new \Exception(
                                'Stok produk tidak tersedia'
                            );
                        }

                        // ❌ stok tidak cukup
                        if ($stok->stok_total < $item->qty_permintaan) {
                            throw new \Exception(
                                'Stok produk tidak mencukupi'
                            );
                        }

                        // ✅ kurangi stok
                        $stok->decrement('stok_total', $item->qty_permintaan);
                    }

                    Pengiriman::create([
                        'permintaan_id' => $permintaan->permintaan_id,
                        'tanggal_kirim' => Carbon::now()->addDays(1),
                        'status_pengiriman' => 'Diproses',
                    ]);
                }

                // ✅ Update status permintaan
                $permintaan->update([
                    'status_permintaan' => $request->status
                ]);
            });

            return back()->with('success', 'Status permintaan berhasil diperbarui');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
