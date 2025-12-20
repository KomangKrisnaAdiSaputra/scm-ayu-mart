<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\StokGudang;
use App\Models\PurchaseOrder;
use App\Models\PermintaanCabang;

class DashboardGudangController extends Controller
{
    public function index()
    {
        return view('gudang.dashboard', [
            'stokMinimum' => StokGudang::whereColumn('stok_total','<=','stok_minimum')->count(),
            'poAktif' => PurchaseOrder::whereIn('status_po', [
                'Menunggu Persetujuan',
                'Disetujui Manajer',
                'Dikirim Supplier'
            ])->count(),
            'permintaanCabang' => PermintaanCabang::where('status_permintaan','Menunggu')->count()
        ]);
    }
}