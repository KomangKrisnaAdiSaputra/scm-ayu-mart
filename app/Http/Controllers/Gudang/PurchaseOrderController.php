<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\DetailPurchaseOrder;
use App\Models\Produk;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function create()
    {
        return view('gudang.po.create', [
            'produk' => Produk::all()
        ]);
    }

    public function store(Request $request)
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $request->supplier_id,
            'tanggal_po' => now(),
            'status_po' => 'Menunggu Persetujuan',
            'status_pembayaran' => 'Belum Bayar'
        ]);

        foreach ($request->produk as $item) {
            DetailPurchaseOrder::create([
                'po_id' => $po->po_id,
                'produk_id' => $item['produk_id'],
                'qty' => $item['qty'],
                'harga' => $item['harga']
            ]);
        }

        return redirect()->back()->with('success','PO berhasil dikirim ke manajer');
    }
}
