<?php

namespace App\Http\Controllers;

use App\Models\DetailPurchaseOrder;
use App\Models\Produk;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $po = PurchaseOrder::with(['supplier', 'detail', 'detail.produk'])
            ->byRole(auth()->user()->role)
            ->when($search, function ($q) use ($search) {
                $q->where('po_id', 'like', "%$search%")
                    ->orWhere('status_po', 'like', "%$search%")
                    ->orWhere('status_pembayaran', 'like', "%$search%")
                    ->orWhereDate('tanggal_po', $search);
            })
            ->orderBy('updated_at', 'desc')
            ->orderBy('tanggal_po', 'desc')
            ->get();

        return view('purchase_order.index', compact('po', 'search'));
    }

    public function create()
    {
        return view('purchase_order.form', [
            'po' => null,
            'supplier' => Supplier::all(),
            'produk' => Produk::where('status_produk', 'aktif')->get(),
            'details' => []
        ]);
    }

    public function edit(PurchaseOrder $po)
    {
        if (!in_array($po->status_po, ['Draft', 'Menunggu Persetujuan'])) {
            return redirect()->back()->with('error', 'PO tidak bisa diedit');
        }

        return view('purchase_order.form', [
            'po' => $po,
            'supplier' => Supplier::all(),
            'produk' => Produk::where('status_produk', 'aktif')->get(),
            'details' => $po->detail
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'produk' => 'required|array|min:1',
            'produk.*.produk_id' => 'required',
            'produk.*.qty' => 'required|numeric|min:1',
            'produk.*.harga' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $produkMerged = $this->mergeProduk($request->produk);

            $total = 0;
            foreach ($produkMerged as $item) {
                $total += $item['qty'] * $item['harga'];
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'tanggal_po' => now(),
                'total_po' => $total,
                'status_po' => 'Draft',
                'status_pembayaran' => 'Belum Bayar',
            ]);

            foreach ($produkMerged as $item) {
                DetailPurchaseOrder::create([
                    'po_id' => $po->po_id,
                    'produk_id' => $item['produk_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                ]);
            }

            DB::commit();
            return redirect()->route('purchaseorder')
                ->with('success', 'Purchase Order berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required',
            'produk' => 'required|array|min:1',
            'produk.*.produk_id' => 'required',
            'produk.*.qty' => 'required|numeric|min:1',
            'produk.*.harga' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $po = PurchaseOrder::findOrFail($id);

            $produkMerged = $this->mergeProduk($request->produk);

            DetailPurchaseOrder::where('po_id', $po->po_id)->delete();

            $total = 0;
            foreach ($produkMerged as $item) {
                $total += $item['qty'] * $item['harga'];

                DetailPurchaseOrder::create([
                    'po_id' => $po->po_id,
                    'produk_id' => $item['produk_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                ]);
            }

            $po->update([
                'supplier_id' => $request->supplier_id,
                'total_po' => $total,
            ]);

            DB::commit();
            return redirect()->route('purchaseorder')
                ->with('success', 'Purchase Order berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    public function updateStatus(Request $request, PurchaseOrder $po)
    {
        $request->validate([
            'status_po' => 'required|string'
        ]);

        $role = auth()->user()->role;
        $currentStatus = $po->status_po;
        $newStatus = $request->status_po;

        $rules = [
            'Gudang' => [
                'Draft' => ['Draft', 'Menunggu Persetujuan'],
                'Dikirim Supplier' => ['Selesai'],
                'Menunggu Persetujuan' => ['Draft', 'Menunggu Persetujuan'],
            ],
            'Manajer' => [
                'Menunggu Persetujuan' => ['Disetujui Manajer', 'Ditolak Manajer'],
            ],
            'Supplier' => [
                'Disetujui Manajer' => ['Diterima Supplier', 'Ditolak Supplier'],
                'Diterima Supplier' => ['Dikirim Supplier'],
            ],
        ];

        // ❌ Role tidak punya aturan
        if (!isset($rules[$role][$currentStatus])) {
            abort(403, 'Anda tidak diizinkan mengubah status ini');
        }

        // ❌ Status tujuan tidak valid
        if (!in_array($newStatus, $rules[$role][$currentStatus])) {
            abort(403, 'Perubahan status tidak valid');
        }

        // ✅ Update
        $po->update([
            'status_po' => $newStatus
        ]);

        return redirect()->back()->with([
            'success' => 'Status berhasil diperbarui menjadi ' . $newStatus
        ]);
    }


    private function mergeProduk(array $produk)
    {
        $merged = [];

        foreach ($produk as $item) {
            $id = $item['produk_id'];

            if (!isset($merged[$id])) {
                $merged[$id] = [
                    'produk_id' => $id,
                    'qty' => (int) $item['qty'],
                    'harga' => (int) $item['harga'],
                ];
            } else {
                $merged[$id]['qty'] += (int) $item['qty'];
            }
        }

        return array_values($merged);
    }
}
