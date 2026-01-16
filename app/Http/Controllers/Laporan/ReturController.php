<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Retur;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $query = Retur::with(['purchaseOrder.supplier']);

        // 🔍 SEARCH (kode PO / supplier / alasan / status)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('alasan', 'like', "%$search%")
                    ->orWhere('status_retur', 'like', "%$search%")
                    ->orWhereHas('purchaseOrder', function ($po) use ($search) {
                        $po->where('kode_po', 'like', "%$search%")
                            ->orWhereHas('supplier', function ($s) use ($search) {
                                $s->where('nama_supplier', 'like', "%$search%");
                            });
                    });
            });
        }

        // 📅 FILTER TANGGAL
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_retur', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_retur', '<=', $request->tanggal_akhir);
        }

        // 🏢 FILTER SUPPLIER
        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        // 📌 FILTER STATUS RETUR
        if ($request->filled('status_retur')) {
            $query->where('status_retur', $request->status_retur);
        }

        $returs = $query
            ->orderBy('tanggal_retur', 'desc')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('laporan.retur.index', compact('returs', 'suppliers'));
    }

    public function exportPdf(Request $request)
    {
        $query = Retur::with(['purchaseOrder.supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('alasan', 'like', "%$search%")
                    ->orWhere('status_retur', 'like', "%$search%")
                    ->orWhereHas('purchaseOrder', function ($po) use ($search) {
                        $po->where('kode_po', 'like', "%$search%")
                            ->orWhereHas('supplier', function ($s) use ($search) {
                                $s->where('nama_supplier', 'like', "%$search%");
                            });
                    });
            });
        }

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_retur', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_retur', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        if ($request->filled('status_retur')) {
            $query->where('status_retur', $request->status_retur);
        }

        $returs = $query->get();

        $pdf = Pdf::loadView('laporan.retur.pdf', compact('returs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-retur.pdf');
    }
}
