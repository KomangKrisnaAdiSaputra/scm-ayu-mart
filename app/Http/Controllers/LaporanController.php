<?php

namespace App\Http\Controllers;

use App\Models\Integrasi\TbCabang;
use App\Models\Integrasi\TbProduk;
use App\Models\Pengiriman;
use App\Models\PermintaanCabang;
use App\Models\PurchaseOrder;
use App\Models\Retur;
use App\Models\StatusKurir;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->from;
        $to     = $request->to;
        $statusPO = $request->status_po;
        $statusPengiriman = $request->status_pengiriman;

        /* =========================
         | BASE FILTER PO
         ========================= */
        $poQuery = PurchaseOrder::with('supplier');

        if ($from && $to) {
            $poQuery->whereBetween('tanggal_po', [$from, $to]);
        }

        if ($statusPO) {
            $poQuery->where('status_po', $statusPO);
        }

        $laporanPO = $poQuery->get();

        $totalNilaiPO = $laporanPO->sum('total_po');

        /* =========================
         | PO PER SUPPLIER
         ========================= */
        $laporanPOSupplier = Supplier::select(
            'supplier.supplier_id',
            'supplier.nama_supplier',
            DB::raw('COUNT(purchase_order.po_id) as total_po')
        )
            ->leftJoin('purchase_order', 'purchase_order.supplier_id', '=', 'supplier.supplier_id')
            ->when(
                $from && $to,
                fn($q) =>
                $q->whereBetween('purchase_order.tanggal_po', [$from, $to])
            )
            ->when(
                $statusPO,
                fn($q) =>
                $q->where('purchase_order.status_po', $statusPO)
            )
            ->groupBy('supplier.supplier_id', 'supplier.nama_supplier')
            ->orderByDesc('total_po')
            ->get();

        $laporanRetur = Retur::with([
            'purchaseOrder.supplier',
            'tb_payment'
        ])->when($request->from && $request->to, function ($q) use ($request) {
            $q->whereBetween('tanggal_retur', [$request->from, $request->to]);
        })->when($request->status_retur, function ($q) use ($request) {
            $q->where('status_retur', $request->status_retur);
        })->orderBy('tanggal_retur', 'desc')->get();

        /* =========================
         | RETUR PER SUPPLIER
         ========================= */
        $laporanReturSupplier = Supplier::select(
            'supplier.nama_supplier',
            DB::raw('COUNT(retur.retur_id) as total_retur')
        )
            ->leftJoin('purchase_order', 'purchase_order.supplier_id', '=', 'supplier.supplier_id')
            ->leftJoin('retur', 'retur.po_id', '=', 'purchase_order.po_id')
            ->when(
                $from && $to,
                fn($q) =>
                $q->whereBetween('retur.tanggal_retur', [$from, $to])
            )
            ->groupBy('supplier.nama_supplier')
            ->orderByDesc('total_retur')
            ->get();

        /* =========================
         | PENGIRIMAN
         ========================= */
        $laporanPengiriman = StatusKurir::select(
            'status_kurir',
            DB::raw('COUNT(*) as total')
        )
            ->whereIn('status_kurir', ['Terkirim', 'Gagal'])
            ->whereHas('pengiriman', function ($q) use ($from, $to, $statusPengiriman) {

                if ($from && $to) {
                    $q->whereBetween('tanggal_kirim', [
                        \Carbon\Carbon::parse($from)->startOfDay(),
                        \Carbon\Carbon::parse($to)->endOfDay()
                    ]);
                }

                if (!is_null($statusPengiriman)) {
                    $q->where('status_pengiriman', $statusPengiriman);
                }
            })
            ->groupBy('status_kurir')
            ->get();

        /* =========================
         | PRODUK PER JENIS
         ========================= */
        $laporanProduk = TbProduk::select(
            'id_jenis',
            DB::raw('COUNT(id_produk) as total_produk')
        )
            ->with('jenis')
            ->groupBy('id_jenis')
            ->get();

        /* =========================
         | PERMINTAAN CABANG
         ========================= */

        $allCabang = TbCabang::all();
        $laporanPermintaanCabang = PermintaanCabang::select(
            'cabang_id',
            DB::raw('COUNT(permintaan_id) as total_permintaan')
        )->when(
            $from && $to,
            fn($q) =>
            $q->whereBetween('tanggal_permintaan', [$from, $to])
        )->groupBy('cabang_id')->get()->map(function ($item) use ($allCabang) {
            $cabang = $allCabang->firstWhere('id_cabang', $item->cabang_id);
            $item->nama_cabang = $cabang ? $cabang->nama_cabang : 'Unknown';
            return $item;
        });

        return view('laporan.index', compact(
            'laporanPO',
            'totalNilaiPO',
            'laporanPOSupplier',
            'laporanRetur',
            'laporanReturSupplier',
            'laporanPengiriman',
            'laporanProduk',
            'laporanPermintaanCabang'
        ));
    }

    /* =========================
     | GENERATE PDF
     ========================= */
    public function exportPdf(Request $request)
    {
        // pakai logic yang sama dengan index
        $data = $this->index($request)->getData();

        $pdf = PDF::loadView('laporan.pdf', (array) $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('laporan.pdf');
    }
}
