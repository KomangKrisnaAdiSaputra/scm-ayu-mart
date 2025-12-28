<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Retur;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $retur = Retur::with([
            'produk',
            'purchaseOrder'
        ])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('produk', function ($qp) use ($search) {
                    $qp->where('nama_produk', 'like', "%$search%")
                        ->orWhere('kode_produk', 'like', "%$search%");
                })
                    ->orWhereHas('purchaseOrder', function ($qp) use ($search) {
                        $qp->where('kode_po', 'like', "%$search%");
                    })
                    ->orWhere('status_retur', 'like', "%$search%");
            })
            ->orderBy('tanggal_retur', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('retur.index', compact('retur'));
    }

    function create()
    {
        $poList = PurchaseOrder::where('status_po', 'Dikirim Supplier')
            ->orderBy('tanggal_po', 'desc')
            ->get();

        return view('retur.create', compact('poList'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $hasRetur = false;

            foreach ($request->items as $poId => $products) {

                foreach ($products as $item) {

                    if (!isset($item['checked'])) continue;

                    // ❌ Jika dicentang tapi qty / alasan kosong
                    if (
                        empty($item['qty_retur']) ||
                        empty($item['alasan'])
                    ) {
                        throw new \Exception(
                            'Qty retur dan alasan wajib diisi untuk produk yang dipilih'
                        );
                    }

                    if ($item['qty_retur'] <= 0) {
                        throw new \Exception('Qty retur harus lebih dari 0');
                    }

                    Retur::create([
                        'po_id'         => $poId,
                        'produk_id'     => $item['produk_id'],
                        'qty_retur'     => $item['qty_retur'],
                        'alasan'        => $item['alasan'],
                        'tanggal_retur' => now(),
                        'status_retur'  => 'Menunggu Konfirmasi',
                    ]);

                    $hasRetur = true;
                }

                if ($hasRetur) {
                    PurchaseOrder::where('po_id', $poId)
                        ->update(['status_po' => 'Retur']);
                }
            }

            DB::commit();

            return redirect()->route('retur')
                ->with('success', 'Retur berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function terima($id)
    {
        DB::beginTransaction();

        try {
            $retur = Retur::with(['purchaseOrder.detail', 'produk'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($retur->status_retur !== 'Menunggu Konfirmasi') {
                throw new \Exception('Retur sudah diproses');
            }

            $po = $retur->purchaseOrder;

            // Ambil detail PO produk terkait
            $poDetail = $po->detail()
                ->where('produk_id', $retur->produk_id)
                ->lockForUpdate()
                ->first();

            if (!$poDetail) {
                throw new \Exception('Detail PO tidak ditemukan');
            }

            $qtyPo    = $poDetail->qty;
            $qtyRetur = $retur->qty_retur;

            if ($qtyRetur > $qtyPo) {
                throw new \Exception('Qty retur melebihi qty PO');
            }

            /**
             * 1️⃣ Hitung qty masuk gudang
             */
            $qtyMasukGudang = $qtyPo - $qtyRetur;

            /**
             * 2️⃣ Tambah stok gudang
             */
            $stok = StokGudang::where('produk_id', $retur->produk_id)
                ->lockForUpdate()
                ->first();

            if (!$stok) {
                throw new \Exception('Stok gudang tidak ditemukan');
            }

            $stok->increment('stok_total', $qtyMasukGudang);

            /**
             * 3️⃣ Update qty PO detail
             */
            $poDetail->update([
                'qty' => $qtyMasukGudang
            ]);

            /**
             * 4️⃣ Hitung ulang TOTAL PO
             */
            $totalPoBaru = $po->detail->sum(function ($item) {
                return $item->qty * $item->harga;
            });

            $po->update([
                'total_po' => $totalPoBaru,
                'status_po' => 'Retur'
            ]);

            /**
             * 5️⃣ Update status retur
             */
            $retur->update([
                'status_retur' => 'Diterima'
            ]);

            DB::commit();

            return back()->with('success', 'Retur diterima, stok & total PO diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }


    public function tolak($id)
    {
        DB::beginTransaction();

        try {
            $retur = Retur::with('purchaseOrder')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($retur->status_retur !== 'Menunggu Konfirmasi') {
                throw new \Exception('Retur sudah diproses');
            }

            $retur->update([
                'status_retur' => 'Ditolak'
            ]);

            // PO kembali ke kondisi sebelumnya
            $retur->purchaseOrder->update([
                'status_po' => 'Dikirim Supplier'
            ]);

            DB::commit();

            return back()->with('success', 'Retur ditolak');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
