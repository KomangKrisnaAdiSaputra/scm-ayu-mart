<?php

namespace App\Http\Controllers;

use App\Models\DetailPermintaanCabang;
use App\Models\Integrasi\TbCabang;
use App\Models\PurchaseOrder;
use App\Models\InvoicePayment;
use App\Models\Supplier;
use App\Models\StokGudang;
use App\Models\Integrasi\TbProduk;
use App\Models\Integrasi\TbStokCabang;
use App\Models\Pengiriman;
use App\Models\Retur;
use App\Models\StatusKurir;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role;

        /* ===============================
         * CARD RINGKASAN
         * =============================== */
        $totalPO = PurchaseOrder::count();

        $totalPOBulanIni = PurchaseOrder::whereMonth('tanggal_po', now()->month)
            ->whereYear('tanggal_po', now()->year)
            ->count();

        $totalPengeluaranBulanIni = InvoicePayment::whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('jumlah_bayar');

        $poSelesai = PurchaseOrder::where('status_po', 'Selesai')->count();

        /* ===============================
         * GRAFIK LINE – TREN PEMBELIAN
         * =============================== */
        $trendPembelian = PurchaseOrder::selectRaw('
                MONTH(tanggal_po) as bulan,
                COUNT(*) as jumlah_po,
                SUM(total_po) as total_nilai
            ')
            ->whereYear('tanggal_po', now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        /* ===============================
         * DONUT – STATUS PO
         * =============================== */
        $statusPO = PurchaseOrder::selectRaw("
            CASE
                WHEN status_po = 'Menunggu Persetujuan' THEN 'Menunggu'
                WHEN status_po = 'Disetujui Manajer' THEN 'Disetujui'
                WHEN status_po = 'Dikirim Supplier' THEN 'Dikirim'
                WHEN status_po = 'Selesai' THEN 'Selesai'
                ELSE 'Lainnya'
            END as status_group,
            COUNT(*) as total
        ")
            ->groupBy('status_group')
            ->get();

        /* ===============================
         * TOP SUPPLIER
         * =============================== */
        $topSuppliers = Supplier::withCount([
            'purchaseOrders as total_po'
        ])
            ->withSum('purchaseOrders as total_nilai', 'total_po')
            ->orderByDesc('total_po')
            ->limit(5)
            ->get();

        /* ===============================
         * STOK GUDANG (DASHBOARD LAMA)
         * =============================== */
        $stokGudang = collect();
        $produkGudang = collect();

        // if ($role === 'Gudang') {
        $stokGudang = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkGudang = TbProduk::whereIn(
            'id_produk',
            $stokGudang->pluck('produk_id')
        )->get();
        // }

        /* ===============================
         * STOK CABANG (DASHBOARD LAMA)
         * =============================== */
        $stokCabang = collect();
        $produkCabang = collect();

        // if ($role === 'Cabang') {
        $stokCabang = TbStokCabang::whereColumn('total_stok', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkCabang = TbProduk::whereIn(
            'id_produk',
            $stokCabang->pluck('produk_id')
        )->get();
        // }

        $integrationDb = config('database.connections.mysqlIntegration.database');

        /* ===============================
 * DISTRIBUSI STOK GUDANG PER JENIS
 * =============================== */
        $stokPerJenis = StokGudang::selectRaw('
        tb_jenis.nama_jenis,
        SUM(stok_gudang.stok_total) as total_stok
    ')
            ->join("$integrationDb.tb_produk as tb_produk", 'tb_produk.id_produk', '=', 'stok_gudang.produk_id')
            ->join("$integrationDb.tb_jenis as tb_jenis", 'tb_jenis.id_jenis', '=', 'tb_produk.id_jenis')
            ->groupBy('tb_jenis.nama_jenis')
            ->orderByDesc('total_stok')
            ->get();

        /* ===============================
 * PRODUK STOK RENDAH
 * =============================== */
        $produkStokRendah = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')
            ->join("$integrationDb.tb_produk as tb_produk", 'tb_produk.id_produk', '=', 'stok_gudang.produk_id')
            ->join("$integrationDb.tb_jenis as tb_jenis", 'tb_jenis.id_jenis', '=', 'tb_produk.id_jenis')
            ->select(
                'tb_produk.nama_produk',
                'tb_jenis.nama_jenis',
                'stok_gudang.stok_total',
                'stok_gudang.stok_minimum'
            )
            ->orderBy('stok_total')
            ->limit(5)
            ->get();

        /* ===============================
 * PRODUK STOK TINGGI
 * =============================== */
        $produkStokTinggi = StokGudang::whereColumn('stok_total', '>', DB::raw('stok_minimum * 2'))
            ->join("$integrationDb.tb_produk as tb_produk", 'tb_produk.id_produk', '=', 'stok_gudang.produk_id')
            ->join("$integrationDb.tb_jenis as tb_jenis", 'tb_jenis.id_jenis', '=', 'tb_produk.id_jenis')
            ->select(
                'tb_produk.nama_produk',
                'tb_jenis.nama_jenis',
                'stok_gudang.stok_total',
                'stok_gudang.stok_minimum'
            )
            ->orderByDesc('stok_total')
            ->limit(5)
            ->get();

        $topProdukRequest = DetailPermintaanCabang::select(
            'produk_id',
            DB::raw('SUM(qty_permintaan) as total_qty')
        )
            ->groupBy('produk_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $produkIds = $topProdukRequest->pluck('produk_id');

        $produkMap = TbProduk::whereIn('id_produk', $produkIds)
            ->get()
            ->keyBy('id_produk');

        $topCabangRequest = DB::table('permintaan_cabang')
            ->select('cabang_id', DB::raw('COUNT(*) as total_request'))
            ->groupBy('cabang_id')
            ->orderByDesc('total_request')
            ->limit(5)
            ->get();

        $cabangMap = TbCabang::whereIn(
            'id_cabang',
            $topCabangRequest->pluck('cabang_id')
        )->get()->keyBy('id_cabang');

        $permintaanBulanan = DetailPermintaanCabang::join(
            'permintaan_cabang',
            'detail_permintaan_cabang.permintaan_id',
            '=',
            'permintaan_cabang.permintaan_id'
        )
            ->select(
                DB::raw("DATE_FORMAT(permintaan_cabang.created_at, '%Y-%m') as bulan"),
                DB::raw('SUM(qty_permintaan) as total_qty')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        /* ===============================
 * LAPORAN PENGIRIMAN BARANG KE CABANG
 * =============================== */

        // KPI Status Pengiriman
        $pengirimanStatus = Pengiriman::select(
            'status_pengiriman',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('status_pengiriman')
            ->get()
            ->keyBy('status_pengiriman');

        // Hitung KPI
        $pengirimanSelesai = $pengirimanStatus['Selesai']->total ?? 0;
        $pengirimanDiproses = $pengirimanStatus['Diproses']->total ?? 0;
        $pengirimanDikirim = $pengirimanStatus['Dikirim']->total ?? 0;
        $pengirimanDiterima = $pengirimanStatus['Diterima']->total ?? 0;
        $pengirimanGagal = $pengirimanStatus['Gagal']->total ?? 0;

        // Rata-rata waktu pengiriman (hari)
        $rataWaktuPengiriman = StatusKurir::join(
            'pengiriman',
            'status_kurir.pengiriman_id',
            '=',
            'pengiriman.pengiriman_id'
        )
            ->where('status_kurir.status_kurir', 'Diterima')
            ->select(DB::raw('AVG(DATEDIFF(status_kurir.waktu_update, pengiriman.tanggal_kirim)) as rata_hari'))
            ->value('rata_hari');

        // Data grafik horizontal
        $grafikPengiriman = collect([
            'Diproses' => $pengirimanDiproses,
            'Dikirim' => $pengirimanDikirim,
            'Diterima' => $pengirimanDiterima,
            'Selesai' => $pengirimanSelesai,
            'Gagal' => $pengirimanGagal,
        ]);


        /* ===============================
 * LAPORAN RETUR SUPPLIER
 * =============================== */

        // filter periode (tahun berjalan)
        $periodeTahun = now()->year;

        /* 1. TOTAL RETUR PER PERIODE */
        $totalRetur = Retur::whereYear('tanggal_retur', $periodeTahun)->count();

        /* 2. ALASAN RETUR (PIE CHART) */
        $alasanRetur = Retur::select(
            'alasan',
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('tanggal_retur', $periodeTahun)
            ->groupBy('alasan')
            ->orderByDesc('total')
            ->get();

        /* 3. SUPPLIER DENGAN RETUR TERBANYAK */
        $supplierRetur = Retur::join('purchase_order', 'retur.po_id', '=', 'purchase_order.po_id')
            ->join('supplier', 'purchase_order.supplier_id', '=', 'supplier.supplier_id')
            ->select(
                'supplier.nama_supplier',
                DB::raw('COUNT(retur.retur_id) as total_retur'),
                DB::raw('SUM(retur.qty_retur) as total_qty')
            )
            ->groupBy('supplier.nama_supplier')
            ->orderByDesc('total_retur')
            ->limit(5)
            ->get();

        /* 4. TABEL RETUR DETAIL */
        $tabelRetur = Retur::with(['purchaseOrder.supplier', 'tb_payment'])
            ->orderByDesc('tanggal_retur')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'role',
            'totalPO',
            'totalPOBulanIni',
            'totalPengeluaranBulanIni',
            'poSelesai',
            'trendPembelian',
            'statusPO',
            'topSuppliers',
            'stokGudang',
            'produkGudang',
            'stokCabang',
            'produkCabang',
            'stokPerJenis',
            'produkStokRendah',
            'produkStokTinggi',
            'topProdukRequest',
            'produkMap',
            'topCabangRequest',
            'cabangMap',
            'permintaanBulanan',
            'pengirimanSelesai',
            'pengirimanDiproses',
            'pengirimanDikirim',
            'pengirimanDiterima',
            'pengirimanGagal',
            'rataWaktuPengiriman',
            'grafikPengiriman',
            'totalRetur',
            'alasanRetur',
            'supplierRetur',
            'tabelRetur',

        ));
    }
}
