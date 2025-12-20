<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Produk;
use App\Models\Supplier;

class DashboardManajerController extends Controller
{
    public function index()
    {
        return view('manajer.dashboard', [
            'totalPO' => PurchaseOrder::count(),
            'totalProduk' => Produk::count(),
            'totalSupplier' => Supplier::count()
        ]);
    }
}
