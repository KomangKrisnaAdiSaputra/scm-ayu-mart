<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');
        // 🔍 SEARCH (kode PO / supplier / status)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode_po', 'like', "%$search%")
                    ->orWhere('status_po', 'like', "%$search%")
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->where('nama_supplier', 'like', "%$search%");
                    });
            });
        }
        // ================= FILTER =================
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_po', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_po', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status_po')) {
            $query->where('status_po', $request->status_po);
        }

        $purchaseOrders = $query->orderBy('tanggal_po', 'desc')->get();

        return view('laporan.purchase_order.index', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => Supplier::all(),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        // 🔍 SEARCH (kode PO / supplier / status)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode_po', 'like', "%$search%")
                    ->orWhere('status_po', 'like', "%$search%")
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->where('nama_supplier', 'like', "%$search%");
                    });
            });
        }

        // filter sama seperti index
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_po', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_po', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status_po')) {
            $query->where('status_po', $request->status_po);
        }

        $purchaseOrders = $query->orderBy('tanggal_po')->get();

        $pdf = PDF::loadView('laporan.purchase_order.pdf', [
            'purchaseOrders' => $purchaseOrders,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('laporan-purchase-order.pdf');
    }
}
