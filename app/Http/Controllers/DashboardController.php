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
use App\Models\PermintaanCabang;
use App\Models\Retur;
use App\Models\StatusKurir;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $datas = [
            "Supplier" => "dashSupplier",
            "Kurir" => "dashKurir",
            "Cabang" => "dashCabang",
            "Manajer" => "dashManajer",
            "Gudang" => "dashGudang",
            "Owner" => "dashOwner",
        ];
        $funcSection = $datas[auth()->user()->role] ?? null;
        $sections = !$funcSection ? [] : $this->$funcSection();
        return view('dashboard', compact('sections'));
    }

    function dashSupplier()
    {
        $userLogin = auth()->user();
        $supplier = Supplier::where('users_id', $userLogin->users_id)->first();
        $periodeTahun = now()->year;

        // ===============================
        // BASE DATA
        // ===============================
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->supplier_id)->get();
        $retur = Retur::with('purchaseOrder')->whereHas('purchaseOrder', fn($query) => $query->where('supplier_id', $supplier->supplier_id))->whereYear('tanggal_retur', $periodeTahun)->get();

        // ===============================
        // SUMMARY
        // ===============================
        $totalPO         = $purchaseOrders->count();
        $poSelesai       = $purchaseOrders->where('status_po', 'Selesai')->count();
        $totalPOBulanIni = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now()))
            ->count();

        $totalRetur = $retur->count();

        // ===============================
        // STATUS PO (DONUT)
        // ===============================
        $statusPO = $purchaseOrders
            ->groupBy(fn($po) => match ($po->status_po) {
                'Menunggu Persetujuan' => 'Menunggu',
                'Disetujui Manajer'    => 'Disetujui',
                'Dikirim Supplier'    => 'Dikirim',
                'Selesai'             => 'Selesai',
                default               => 'Lainnya',
            })
            ->map(fn($items, $status) => [
                'status_group' => $status,
                'total'        => $items->count(),
            ])
            ->values();

        // ===============================
        // JENIS RETUR (DONUT)
        // ===============================
        $jenisRetur = $retur
            ->groupBy('payment')
            ->map(fn($items, $key) => [
                'jenis' => $key == 1 ? 'Dikembalikan Barang' : 'Dikembalikan Dana',
                'total'  => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        // ===============================
        // ALASAN RETUR (DONUT)
        // ===============================
        $alasanRetur = $retur
            ->groupBy('alasan')
            ->map(fn($items, $key) => [
                'alasan' => $key ?? 'Tanpa Alasan',
                'total'  => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $donutColors = [
            '#dc3545',
            '#0d6efd',
            '#ffc107',
            '#198754',
            '#6f42c1',
            '#20c997',
        ];

        // ===============================
        // RETURN DASHBOARD CONFIG
        // ===============================
        return collect([

            // ===== CARDS =====
            [
                'type'    => 'cards',
                'per_row' => 4,
                'items'   => [
                    ['bg' => 'bg-primary', 'icon' => 'shopping-cart', 'header' => 'Total PO', 'body' => $totalPO],
                    ['bg' => 'bg-info', 'icon' => 'calendar', 'header' => 'PO Bulan Ini', 'body' => $totalPOBulanIni],
                    ['bg' => 'bg-success', 'icon' => 'check-circle', 'header' => 'PO Selesai', 'body' => $poSelesai],
                    ['bg' => 'bg-danger', 'icon' => 'undo', 'header' => 'Total Retur', 'body' => $totalRetur],
                ],
            ],

            ['type' => 'title', 'title' => 'Diagram'],

            // ===== DONUT =====
            [
                'type'    => 'donut',
                'per_row' => 4,
                'items'   => [
                    [
                        'id'    => 'statusPO',
                        'title' => 'Status Purchase Order',
                        'chart' => [
                            'labels' => $statusPO->pluck('status_group'),
                            'data'   => $statusPO->pluck('total'),
                            'colors' => $statusPO->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $statusPO->values()->map(fn($r, $i) => [
                            'label' => $r['status_group'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'clipboard-check',
                        ]),
                    ],
                    [
                        'id'    => 'jenisRetur',
                        'title' => 'Jenis Retur Supplier',
                        'chart' => [
                            'labels' => $jenisRetur->pluck('jenis'),
                            'data'   => $jenisRetur->pluck('total'),
                            'colors' => $jenisRetur->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $jenisRetur->values()->map(fn($r, $i) => [
                            'label' => $r['jenis'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'undo-alt',
                        ]),
                    ],

                    [
                        'id'    => 'alasanRetur',
                        'title' => 'Alasan Retur Supplier',
                        'chart' => [
                            'labels' => $alasanRetur->pluck('alasan'),
                            'data'   => $alasanRetur->pluck('total'),
                            'colors' => $alasanRetur->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $alasanRetur->values()->map(fn($r, $i) => [
                            'label' => $r['alasan'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'undo-alt',
                        ]),
                    ],
                ],
            ],
        ]);
    }

    function dashKurir()
    {
        $pengiriman = Pengiriman::all();

        // ===============================
        // SUMMARY
        // ===============================
        $totalPengiriman = $pengiriman->count();

        $totalPengirimanBulanIni = $pengiriman
            ->filter(fn($p) => Carbon::parse($p->tanggal_kirim)->isSameMonth(now()))
            ->count();

        // ===============================
        // STATUS PENGIRIMAN
        // ===============================
        $statusMap = [
            'Diproses' => ['bg' => 'bg-warning', 'icon' => 'cogs'],
            'Dikirim'  => ['bg' => 'bg-info',    'icon' => 'truck'],
            'Diterima' => ['bg' => 'bg-success', 'icon' => 'box-open'],
            'Selesai'  => ['bg' => 'bg-primary', 'icon' => 'check-circle'],
            'Gagal'    => ['bg' => 'bg-danger',  'icon' => 'times-circle'],
        ];

        $statusPengiriman = $pengiriman
            ->groupBy('status_pengiriman')
            ->map(fn($items, $status) => [
                'status' => $status,
                'total'  => $items->count(),
            ])
            ->values();

        $donutColors = [
            '#ffc107',
            '#0d6efd',
            '#198754',
            '#0d6efd',
            '#dc3545',
        ];

        // ===============================
        // RETURN DASHBOARD CONFIG
        // ===============================
        return collect([

            // ===== CARDS =====
            [
                'type'    => 'cards',
                'per_row' => 4,
                'items'   => [
                    [
                        'bg'     => 'bg-primary',
                        'icon'   => 'truck',
                        'header' => 'Total Pengiriman',
                        'body'   => $totalPengiriman,
                    ],
                    [
                        'bg'     => 'bg-info',
                        'icon'   => 'calendar',
                        'header' => 'Pengiriman Bulan Ini',
                        'body'   => $totalPengirimanBulanIni,
                    ],
                ],
            ],

            ['type' => 'title', 'title' => 'Status Pengiriman'],

            // ===== DONUT =====
            [
                'type'    => 'donut',
                'per_row' => 3,
                'items'   => [
                    [
                        'id'    => 'statusPengiriman',
                        'title' => 'Distribusi Status Pengiriman',
                        'chart' => [
                            'labels' => $statusPengiriman->pluck('status'),
                            'data'   => $statusPengiriman->pluck('total'),
                            'colors' => $statusPengiriman->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $statusPengiriman->values()->map(function ($r, $i) use ($statusMap, $donutColors) {
                            return [
                                'label' => $r['status'],
                                'value' => $r['total'],
                                'color' => $donutColors[$i % count($donutColors)],
                                'icon'  => $statusMap[$r['status']]['icon'] ?? 'truck',
                            ];
                        }),
                    ],
                ],
            ],
        ]);
    }


    function dashCabang()
    {
        /*
    |------------------------------------------------------------------
    | USER & CABANG
    |------------------------------------------------------------------
    */
        $user   = auth()->user();
        $cabang = TbCabang::where('users_id', $user->users_id)->first();

        /*
    |------------------------------------------------------------------
    | PERMINTAAN CABANG
    |------------------------------------------------------------------
    */
        $permintaanCabang = PermintaanCabang::when(
            $user->role === 'Cabang',
            fn($q) => $q->where('cabang_id', $cabang?->id_cabang)
        )->get();

        /*
    |------------------------------------------------------------------
    | SUMMARY
    |------------------------------------------------------------------
    */
        $totalPermintaan = $permintaanCabang->count();

        $totalPermintaanBulanIni = $permintaanCabang
            ->filter(fn($p) => Carbon::parse($p->tanggal_permintaan)->isSameMonth(now()))
            ->count();

        /*
    |------------------------------------------------------------------
    | STATUS PERMINTAAN
    |------------------------------------------------------------------
    */
        $statusPermintaanConfig = [
            'Menunggu' => ['bg' => 'bg-warning', 'icon' => 'clock'],
            'Diterima' => ['bg' => 'bg-success', 'icon' => 'check-circle'],
            'Ditolak'  => ['bg' => 'bg-danger',  'icon' => 'times-circle'],
        ];

        $permintaanStatus = $permintaanCabang
            ->groupBy('status_permintaan')
            ->map(fn($items, $status) => [
                'status' => $status,
                'total'  => $items->count(),
            ])
            ->values();

        /*
    |------------------------------------------------------------------
    | PENGIRIMAN
    |------------------------------------------------------------------
    */
        $pengiriman = Pengiriman::whereIn(
            'permintaan_id',
            $permintaanCabang->pluck('permintaan_id')
        )->get();

        $statusPengirimanConfig = [
            'Diproses' => ['bg' => 'bg-warning', 'icon' => 'cogs'],
            'Dikirim'  => ['bg' => 'bg-info',    'icon' => 'truck'],
            'Diterima' => ['bg' => 'bg-success', 'icon' => 'box-open'],
            'Selesai'  => ['bg' => 'bg-primary', 'icon' => 'check-circle'],
            'Gagal'    => ['bg' => 'bg-danger',  'icon' => 'times-circle'],
        ];

        $pengirimanStatus = $pengiriman
            ->groupBy('status_pengiriman')
            ->map(fn($items, $status) => [
                'status' => $status,
                'total'  => $items->count(),
            ])
            ->values();

        /*
    |------------------------------------------------------------------
    | STOK MENIPIS
    |------------------------------------------------------------------
    */
        $stokCabang = TbStokCabang::where("id_cabang", $cabang?->id_cabang)->whereColumn('total_stok', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkCabang = TbProduk::whereIn(
            'id_produk',
            $stokCabang->pluck('id_produk')
        )->get()
            ->keyBy('id_produk');

        $tabelStokMenipis = $stokCabang->map(fn($stok) => [
            'nama_produk' => $produkCabang[$stok->id_produk]->nama_produk ?? '-',
            'stok' => [
                'type'  => 'badge',
                'class' => 'badge-danger',
                'text'  => "{$stok->total_stok} / {$stok->stok_minimum}",
            ],
        ]);

        $donutColors = [
            '#ffc107',
            '#198754',
            '#dc3545',
            '#0d6efd',
            '#6f42c1',
        ];

        /*
    |------------------------------------------------------------------
    | RETURN DASHBOARD CONFIG
    |------------------------------------------------------------------
    */
        return collect([

            // ================= CARDS =================
            [
                'type'    => 'cards',
                'per_row' => 4,
                'items'   => [
                    [
                        'bg'     => 'bg-primary',
                        'icon'   => 'clipboard-list',
                        'header' => 'Total Permintaan',
                        'body'   => $totalPermintaan,
                    ],
                    [
                        'bg'     => 'bg-info',
                        'icon'   => 'calendar',
                        'header' => 'Permintaan Bulan Ini',
                        'body'   => $totalPermintaanBulanIni,
                    ],
                ],
            ],

            // ================= TABLE =================
            [
                'type'    => 'tables',
                'per_row' => 3,
                'items'   => [
                    [
                        'theme' => 'danger',
                        'icon'  => 'exclamation-triangle',
                        'title' => 'Stok Cabang Menipis',
                        'headers' => [
                            ['key' => 'nama_produk', 'label' => 'Produk'],
                            ['key' => 'stok', 'label' => 'Stok'],
                        ],
                        'rows'       => $tabelStokMenipis,
                        'empty_text' => 'Semua stok aman',
                    ],
                ],
            ],

            ['type' => 'title', 'title' => 'Diagram'],

            // ================= DONUT PERMINTAAN =================
            [
                'type'    => 'donut',
                'per_row' => 4,
                'items'   => [
                    [
                        'id'    => 'statusPermintaan',
                        'title' => 'Distribusi Status Permintaan',
                        'chart' => [
                            'labels' => $permintaanStatus->pluck('status'),
                            'data'   => $permintaanStatus->pluck('total'),
                            'colors' => $permintaanStatus->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $permintaanStatus->values()->map(fn($r, $i) => [
                            'label' => $r['status'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => $statusPermintaanConfig[$r['status']]['icon'] ?? 'clipboard',
                        ]),
                    ],
                    [
                        'id'    => 'statusPengiriman',
                        'title' => 'Distribusi Status Pengiriman',
                        'chart' => [
                            'labels' => $pengirimanStatus->pluck('status'),
                            'data'   => $pengirimanStatus->pluck('total'),
                            'colors' => $pengirimanStatus->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $pengirimanStatus->values()->map(fn($r, $i) => [
                            'label' => $r['status'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => $statusPengirimanConfig[$r['status']]['icon'] ?? 'truck',
                        ]),
                    ],
                ],
            ],
        ]);
    }


    function dashManajer()
    {
        $periodeTahun = now()->year;

        // ===============================
        // BASE DATA
        // ===============================
        $purchaseOrders = PurchaseOrder::all();

        // ===============================
        // SUMMARY
        // ===============================
        $totalPO = $purchaseOrders->count();

        $totalPOBulanIni = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now()))
            ->count();

        $totalPengeluaranBulanIni = InvoicePayment::whereHas(
            'invoice',
            fn($q) => $q->where('status_invoice', 'Lunas')
        )->sum('jumlah_bayar');

        $totalRetur    = Retur::count();
        $totalSupplier = Supplier::count();
        $totalCabang   = TbCabang::count();

        // ===============================
        // STATUS PO BULAN INI (CARD)
        // ===============================
        $statusPOConfig = [
            'Menunggu Persetujuan' => ['bg' => 'bg-warning', 'icon' => 'cogs'],
            'Disetujui Manajer'    => ['bg' => 'bg-info', 'icon' => 'truck'],
            'Dikirim Supplier'    => ['bg' => 'bg-success', 'icon' => 'box-open'],
            'Selesai'             => ['bg' => 'bg-primary', 'icon' => 'check-circle'],
        ];

        $statusPOBulanIni = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now()))
            ->groupBy('status_po')
            ->map(fn($items) => $items->count());

        $cardsStatusPO = collect($statusPOConfig)
            ->map(fn($cfg, $status) => [
                'bg'     => $cfg['bg'],
                'icon'   => $cfg['icon'],
                'header' => $status,
                'body'   => $statusPOBulanIni->get($status, 0),
            ])
            ->values();

        // ===============================
        // STATUS PO (DONUT)
        // ===============================
        $statusPOChart = $purchaseOrders
            ->groupBy(fn($po) => match ($po->status_po) {
                'Menunggu Persetujuan' => 'Menunggu',
                'Disetujui Manajer'    => 'Disetujui',
                'Dikirim Supplier'    => 'Dikirim',
                'Selesai'             => 'Selesai',
                default               => 'Lainnya',
            })
            ->map(fn($items, $status) => [
                'status_group' => $status,
                'total'        => $items->count(),
            ])
            ->values();

        // ===============================
        // STOK GUDANG PER JENIS
        // ===============================
        $integrationDb = config('database.connections.mysqlIntegration.database');

        $stokPerJenis = StokGudang::selectRaw("
        tb_jenis.nama_jenis,
        SUM(stok_gudang.stok_total) AS total_stok
    ")
            ->join("$integrationDb.tb_produk as tb_produk", 'tb_produk.id_produk', '=', 'stok_gudang.produk_id')
            ->join("$integrationDb.tb_jenis as tb_jenis", 'tb_jenis.id_jenis', '=', 'tb_produk.id_jenis')
            ->groupBy('tb_jenis.nama_jenis')
            ->orderByDesc('total_stok')
            ->get();

        // ===============================
        // TREND PEMBELIAN
        // ===============================
        $trendPembelian = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->year === $periodeTahun)
            ->groupBy(fn($po) => Carbon::parse($po->tanggal_po)->month)
            ->map(fn($items, $bulan) => [
                'bulan'       => $bulan,
                'jumlah_po'   => $items->count(),
                'total_nilai' => $items->sum('total_po'),
            ])
            ->sortBy('bulan')
            ->values();

        // ===============================
        // TOP SUPPLIER
        // ===============================
        $topSuppliers = Supplier::withCount(['purchaseOrders as total_po'])
            ->withSum('purchaseOrders as total_nilai', 'total_po')
            ->orderByDesc('total_po')
            ->limit(5)
            ->get();

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

        // ===============================
        // DONUT COLORS (SAMA DENGAN OWNER)
        // ===============================
        $donutColors = [
            '#dc3545',
            '#0d6efd',
            '#ffc107',
            '#198754',
            '#6f42c1',
            '#20c997',
        ];

        // ===============================
        // STOK GUDANG MENIPIS
        // ===============================
        $stokGudang = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkGudang = TbProduk::whereIn(
            'id_produk',
            $stokGudang->pluck('produk_id')
        )->get();

        $tabelStokGudangMenipis = $stokGudang->map(function ($stok) use ($produkGudang) {
            $produk = $produkGudang->firstWhere('id_produk', $stok->produk_id);

            return [
                'nama_produk' => $produk?->nama_produk ?? '-',
                'stok' => [
                    'type'  => 'badge',
                    'class' => 'badge-danger',
                    'text'  => "{$stok->stok_total} / {$stok->stok_minimum}",
                ],
            ];
        });

        // ===============================
        // RETURN DASHBOARD
        // ===============================
        return collect([

            /* ================= CARDS ================= */
            [
                'type'    => 'cards',
                'per_row' => 4,
                'items'   => collect([
                    ['bg' => 'bg-primary',   'icon' => 'shopping-cart',   'header' => 'Total PO',         'body' => $totalPO],
                    ['bg' => 'bg-warning',   'icon' => 'undo',            'header' => 'Total Retur',      'body' => $totalRetur],
                    ['bg' => 'bg-secondary', 'icon' => 'store',           'header' => 'Total Cabang',     'body' => $totalCabang],
                    ['bg' => 'bg-dark',      'icon' => 'truck-loading',   'header' => 'Total Supplier',   'body' => $totalSupplier],
                    ['bg' => 'bg-info',      'icon' => 'calendar',        'header' => 'PO Bulan Ini',     'body' => $totalPOBulanIni],
                    ['bg' => 'bg-success',   'icon' => 'money-bill-wave', 'header' => 'Total Uang Keluar', 'body' => number_format($totalPengeluaranBulanIni)],
                ]),
            ],

            /* ================= TABLE ================= */
            [
                'type'    => 'tables',
                'per_row' => 3,
                'items'   => [
                    [
                        'theme' => 'danger',
                        'icon'  => 'exclamation-triangle',
                        'title' => 'Stok Gudang Menipis',
                        'headers' => [
                            ['key' => 'nama_produk', 'label' => 'Produk'],
                            ['key' => 'stok',        'label' => 'Stok'],
                        ],
                        'rows'       => $tabelStokGudangMenipis,
                        'empty_text' => 'Semua stok gudang aman',
                    ],
                ],
            ],
            ['type' => 'title', 'title' => 'Diagram'],

            /* ================= DONUT ================= */
            [
                'type' => 'donut',
                'per_row' => 4,
                'items' => [

                    // Stok per Jenis
                    [
                        'id'    => 'stokPerJenis',
                        'title' => 'Jenis Stok Gudang',
                        'chart' => [
                            'labels' => $stokPerJenis->pluck('nama_jenis'),
                            'data'   => $stokPerJenis->pluck('total_stok'),
                            'colors' => $stokPerJenis->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $stokPerJenis->values()->map(fn($r, $i) => [
                            'label' => $r->nama_jenis,
                            'value' => $r->total_stok,
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'boxes',
                        ]),
                    ],

                    // Status PO
                    [
                        'id'    => 'statusPO',
                        'title' => 'Status Purchase Order',
                        'chart' => [
                            'labels' => $statusPOChart->pluck('status_group'),
                            'data'   => $statusPOChart->pluck('total'),
                            'colors' => $statusPOChart->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $statusPOChart->values()->map(fn($r, $i) => [
                            'label' => $r['status_group'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'clipboard-check',
                        ]),
                    ],
                ],
            ],

            ['type' => 'title', 'title' => 'Grafik'],

            /* ================= LINE CHART ================= */
            [
                'type' => 'charts',
                'per_row' => 3,
                'items' => [
                    [
                        'id' => 'trendPembelian',
                        'type' => 'line',
                        'title' => 'Tren Pembelian Bulanan',
                        'data' => [
                            'labels' => $trendPembelian->pluck('bulan')->map(fn($b) => "Bulan $b"),
                            'datasets' => [
                                ['label' => 'Jumlah PO', 'data' => $trendPembelian->pluck('jumlah_po')],
                                ['label' => 'Total Transaksi', 'data' => $trendPembelian->pluck('total_nilai'), 'yAxisID' => 'y1'],
                            ],
                        ],
                    ],
                ],
            ],

            ['type' => 'title', 'title' => 'Peringkat Supplier'],

            /* ================= TABLE ================= */
            [
                'type' => 'tables',
                'per_row' => 3,
                'items' => [
                    [
                        'theme' => 'primary',
                        'icon' => 'truck',
                        'title' => 'Top 5 Supplier',
                        'headers' => [
                            ['key' => 'nama_supplier', 'label' => 'Supplier'],
                            ['key' => 'total_po', 'label' => 'Total PO'],
                            ['key' => 'total_nilai', 'label' => 'Total Nilai'],
                        ],
                        'rows' => $topSuppliers->map(fn($r) => [
                            'nama_supplier' => $r->nama_supplier,
                            'total_po' => ['type' => 'badge', 'class' => 'badge-info', 'text' => $r->total_po],
                            'total_nilai' => 'Rp ' . number_format($r->total_nilai ?? 0),
                        ]),
                    ],
                    [
                        'theme' => 'warning',
                        'icon' => 'undo-alt',
                        'title' => 'Top 5 Supplier Retur',
                        'headers' => [
                            ['key' => 'nama_supplier', 'label' => 'Supplier'],
                            ['key' => 'total_retur', 'label' => 'Total Retur'],
                            ['key' => 'total_qty', 'label' => 'Total Qty'],
                        ],
                        'rows' => $supplierRetur->map(fn($r) => [
                            'nama_supplier' => $r->nama_supplier,
                            'total_retur' => ['type' => 'badge', 'class' => 'badge-warning', 'text' => $r->total_retur],
                            'total_qty' => ['type' => 'badge', 'class' => 'badge-danger', 'text' => $r->total_qty],
                        ]),
                    ],
                ],
            ],
        ]);
    }



    function dashGudang()
    {
        $periodeTahun = now()->year;

        // ===============================
        // BASE DATA (AMBIL SEKALI)
        // ===============================
        $purchaseOrders   = PurchaseOrder::all();
        // $permintaanCabang = PermintaanCabang::all();

        // ===============================
        // SUMMARY
        // ===============================
        $totalPO = $purchaseOrders->count();

        $poBulanIni = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now()));

        $totalRetur  = Retur::whereYear('tanggal_retur', $periodeTahun)->count();
        $totalCabang = TbCabang::count();

        // ===============================
        // STATUS PO (DONUT)
        // ===============================
        $statusPOChart = $purchaseOrders
            ->groupBy(fn($po) => match ($po->status_po) {
                'Menunggu Persetujuan' => 'Menunggu',
                'Disetujui Manajer'    => 'Disetujui',
                'Dikirim Supplier'    => 'Dikirim',
                'Selesai'             => 'Selesai',
                default               => 'Lainnya',
            })
            ->map(fn($items, $status) => [
                'status_group' => $status,
                'total'        => $items->count(),
            ])
            ->values();

        // ===============================
        // STOK GUDANG PER JENIS
        // ===============================
        $integrationDb = config('database.connections.mysqlIntegration.database');

        $stokPerJenis = StokGudang::selectRaw("
            tb_jenis.nama_jenis,
            SUM(stok_gudang.stok_total) AS total_stok
        ")
            ->join("$integrationDb.tb_produk as tb_produk", 'tb_produk.id_produk', '=', 'stok_gudang.produk_id')
            ->join("$integrationDb.tb_jenis as tb_jenis", 'tb_jenis.id_jenis', '=', 'tb_produk.id_jenis')
            ->groupBy('tb_jenis.nama_jenis')
            ->orderByDesc('total_stok')
            ->get();

        // ===============================
        // STOK GUDANG MENIPIS
        // ===============================
        $stokGudang = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkGudang = TbProduk::whereIn(
            'id_produk',
            $stokGudang->pluck('produk_id')
        )->get();

        $tabelStokGudangMenipis = $stokGudang->map(function ($stok) use ($produkGudang) {
            $produk = $produkGudang->firstWhere('id_produk', $stok->produk_id);

            return [
                'nama_produk' => $produk?->nama_produk ?? '-',
                'stok' => [
                    'type'  => 'badge',
                    'class' => 'badge-danger',
                    'text'  => "{$stok->stok_total} / {$stok->stok_minimum}",
                ],
            ];
        });

        // ===============================
        // DONUT COLORS (GLOBAL)
        // ===============================
        $donutColors = [
            '#dc3545',
            '#0d6efd',
            '#ffc107',
            '#198754',
            '#6f42c1',
            '#20c997',
        ];

        // ===============================
        // PERMINTAAN CABANG
        // ===============================
        $permintaan = PermintaanCabang::select(
            'cabang_id',
            DB::raw('MONTH(tanggal_permintaan) as bulan'),
            DB::raw('COUNT(*) as jumlah')
        )
            ->whereYear('tanggal_permintaan', now()->year)
            ->groupBy('cabang_id', 'bulan')
            ->orderBy('bulan')
            ->get();
        $cabang = TbCabang::select('id_cabang', 'nama_cabang')->get()->keyBy('id_cabang');
        $labels = collect(range(1, 12))->map(fn($b) => "Bulan $b");

        $datasets = $permintaan
            ->groupBy('cabang_id')
            ->map(function ($items, $cabangId) use ($cabang) {
                return [
                    'label'   => $cabang[$cabangId]->nama_cabang ?? "Cabang #$cabangId",
                    'data'    => collect(range(1, 12))
                        ->map(fn($bulan) => $items->firstWhere('bulan', $bulan)->jumlah ?? 0),
                    'tension' => 0.4,
                    'fill'    => false,
                ];
            })
            ->values();

        // ===============================
        // RETURN DASHBOARD
        // ===============================
        return collect([

            /* ================= CARDS ================= */
            [
                'type'    => 'cards',
                'per_row' => 4,
                'items'   => collect([
                    ['bg' => 'bg-primary',   'icon' => 'shopping-cart', 'header' => 'Total Purchase Order', 'body' => $totalPO],
                    ['bg' => 'bg-info',      'icon' => 'calendar',      'header' => 'PO Bulan Ini',          'body' => $poBulanIni->count()],
                    ['bg' => 'bg-warning',   'icon' => 'undo',          'header' => 'Total Retur',           'body' => $totalRetur],
                    ['bg' => 'bg-secondary', 'icon' => 'store',         'header' => 'Total Cabang',          'body' => $totalCabang],
                ]),
            ],
            ['type' => 'title', 'title' => 'Monitoring Stok'],

            /* ================= TABLE ================= */
            [
                'type'    => 'tables',
                'per_row' => 3,
                'items'   => [
                    [
                        'theme' => 'danger',
                        'icon'  => 'exclamation-triangle',
                        'title' => 'Stok Gudang Menipis',
                        'headers' => [
                            ['key' => 'nama_produk', 'label' => 'Produk'],
                            ['key' => 'stok',        'label' => 'Stok'],
                        ],
                        'rows'       => $tabelStokGudangMenipis,
                        'empty_text' => 'Semua stok gudang aman',
                    ],
                ],
            ],
            ['type' => 'title', 'title' => 'Diagram'],

            /* ================= DONUT ================= */
            [
                'type'    => 'donut',
                'per_row' => 4,
                'items'   => [

                    // Jenis Stok Gudang
                    [
                        'id'    => 'stokPerJenis',
                        'title' => 'Jenis Stok Gudang',
                        'chart' => [
                            'labels' => $stokPerJenis->pluck('nama_jenis'),
                            'data'   => $stokPerJenis->pluck('total_stok'),
                            'colors' => $stokPerJenis->values()
                                ->map(fn($_, $i) => $donutColors[$i % count($donutColors)]),
                        ],
                        'details' => $stokPerJenis->values()->map(fn($r, $i) => [
                            'label' => $r->nama_jenis,
                            'value' => $r->total_stok,
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'boxes',
                        ]),
                    ],

                    // Status Purchase Order
                    [
                        'id'    => 'statusPO',
                        'title' => 'Status Purchase Order',
                        'chart' => [
                            'labels' => $statusPOChart->pluck('status_group'),
                            'data'   => $statusPOChart->pluck('total'),
                            'colors' => $statusPOChart->values()
                                ->map(fn($_, $i) => $donutColors[$i % count($donutColors)]),
                        ],
                        'details' => $statusPOChart->values()->map(fn($r, $i) => [
                            'label' => $r['status_group'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon'  => 'clipboard-check',
                        ]),
                    ],
                ],
            ],

            ['type' => 'title', 'title' => 'Grafik'],

            [
                'type' => 'charts',
                'per_row' => 2,
                'items' => [
                    [
                        'id' => 'permintaanCabang',
                        'type' => 'line',
                        'title' => 'Permintaan Cabang per Bulan',
                        'data' => [
                            'labels' => $labels,
                            'datasets' => $datasets,
                        ],
                    ],
                ],
            ],
        ]);
    }



    function dashOwner()
    {
        $periodeTahun = now()->year;

        // ===============================
        // BASE DATA
        // ===============================
        $purchaseOrders = PurchaseOrder::all();
        $retur          = Retur::whereYear('tanggal_retur', $periodeTahun)->get();

        // ===============================
        // SUMMARY
        // ===============================
        $totalPO           = $purchaseOrders->count();
        $totalPOBulanIni   = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now()))
            ->count();

        $totalRetur = $retur->count();

        $totalPengeluaranBulanIni = InvoicePayment::whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('jumlah_bayar');

        $totalSupplier = Supplier::count();
        $totalCabang   = TbCabang::count();

        // ===============================
        // STATUS PO (DONUT)
        // ===============================
        $statusPOChart = $purchaseOrders
            ->groupBy(fn($po) => match ($po->status_po) {
                'Menunggu Persetujuan' => 'Menunggu',
                'Disetujui Manajer'    => 'Disetujui',
                'Dikirim Supplier'    => 'Dikirim',
                'Selesai'             => 'Selesai',
                default               => 'Lainnya',
            })
            ->map(fn($items, $status) => [
                'status_group' => $status,
                'total'        => $items->count(),
            ])
            ->values();

        $statusPengiriman = Pengiriman::all()->groupBy('status_pengiriman')
            ->map(fn($items, $status) => [
                'status' => $status,
                'total'  => $items->count(),
            ])->values();

        // ===============================
        // RETUR (DONUT)
        // ===============================
        $retur = Retur::whereYear('tanggal_retur', $periodeTahun)->get();

        $alasanRetur = $retur
            ->groupBy('alasan')
            ->map(fn($items, $key) => [
                'alasan' => $key ?? 'Tanpa Alasan',
                'total'  => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        // ===============================
        // STOK GUDANG PER JENIS (DONUT)
        // ===============================
        $integrationDb = config('database.connections.mysqlIntegration.database');

        $stokPerJenis = StokGudang::selectRaw("
            tb_jenis.nama_jenis,
            SUM(stok_gudang.stok_total) AS total_stok
        ")
            ->join("$integrationDb.tb_produk as tb_produk", 'tb_produk.id_produk', '=', 'stok_gudang.produk_id')
            ->join("$integrationDb.tb_jenis as tb_jenis", 'tb_jenis.id_jenis', '=', 'tb_produk.id_jenis')
            ->groupBy('tb_jenis.nama_jenis')
            ->orderByDesc('total_stok')
            ->get();

        // ===============================
        // TREND PEMBELIAN
        // ===============================
        $trendPembelian = $purchaseOrders
            ->filter(fn($po) => Carbon::parse($po->tanggal_po)->year === now()->year)
            ->groupBy(fn($po) => Carbon::parse($po->tanggal_po)->month)
            ->map(fn($items, $bulan) => [
                'bulan'       => $bulan,
                'jumlah_po'   => $items->count(),
                'total_nilai' => $items->sum('total_po'),
            ])
            ->sortBy('bulan')
            ->values();

        // ===============================
        // PERMINTAAN CABANG
        // ===============================
        $permintaan = PermintaanCabang::select(
            'cabang_id',
            DB::raw('MONTH(tanggal_permintaan) as bulan'),
            DB::raw('COUNT(*) as jumlah')
        )
            ->whereYear('tanggal_permintaan', now()->year)
            ->groupBy('cabang_id', 'bulan')
            ->orderBy('bulan')
            ->get();

        $cabang = TbCabang::select('id_cabang', 'nama_cabang')->get()->keyBy('id_cabang');

        $labels = collect(range(1, 12))->map(fn($b) => "Bulan $b");

        $datasets = $permintaan
            ->groupBy('cabang_id')
            ->map(function ($items, $cabangId) use ($cabang) {
                return [
                    'label'   => $cabang[$cabangId]->nama_cabang ?? "Cabang #$cabangId",
                    'data'    => collect(range(1, 12))
                        ->map(fn($bulan) => $items->firstWhere('bulan', $bulan)->jumlah ?? 0),
                    'tension' => 0.4,
                    'fill'    => false,
                ];
            })
            ->values();

        // ===============================
        // TOP SUPPLIER
        // ===============================
        $topSuppliers = Supplier::withCount(['purchaseOrders as total_po'])
            ->withSum('purchaseOrders as total_nilai', 'total_po')
            ->orderByDesc('total_po')
            ->limit(5)
            ->get();

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

        $donutColors = [
            '#dc3545', // red
            '#0d6efd', // blue
            '#ffc107', // yellow
            '#198754', // green
            '#6f42c1', // purple
            '#20c997', // teal
        ];
        // ===============================
        // RETURN DASHBOARD CONFIG
        // ===============================
        return collect([
            [
                'type'    => 'cards',
                'per_row' => 4,
                'items'   => [
                    ['bg' => 'bg-primary', 'icon' => 'shopping-cart', 'header' => 'Total PO', 'body' => $totalPO],
                    ['bg' => 'bg-danger', 'icon' => 'undo', 'header' => 'Total Retur', 'body' => $totalRetur],
                    ['bg' => 'bg-secondary', 'icon' => 'store', 'header' => 'Total Cabang', 'body' => $totalCabang],
                    ['bg' => 'bg-dark', 'icon' => 'truck-loading', 'header' => 'Total Supplier', 'body' => $totalSupplier],
                    ['bg' => 'bg-info', 'icon' => 'calendar', 'header' => 'PO Bulan Ini', 'body' => $totalPOBulanIni],
                    ['bg' => 'bg-success', 'icon' => 'money-bill-wave', 'header' => 'Total Uang Keluar', 'body' => number_format($totalPengeluaranBulanIni)],
                ],
            ],

            ['type' => 'title', 'title' => 'Diagram'],

            [
                'type' => 'donut',
                'per_row' => 3,
                'items' => [
                    [
                        'id' => 'stokPerJenis',
                        'title' => 'Jenis Stok Gudang',
                        'chart' => [
                            'labels' => $stokPerJenis->pluck('nama_jenis'),
                            'data' => $stokPerJenis->pluck('total_stok'),
                            'colors' => $stokPerJenis->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $stokPerJenis->map(fn($r, $i) => [
                            'label' => $r->nama_jenis,
                            'value' => $r->total_stok,
                            'color' =>  $donutColors[$i % count($donutColors)],
                            'icon' => 'boxes',
                        ]),
                    ],
                    [
                        'id' => 'statusPO',
                        'title' => 'Status Purchase Order',
                        'chart' => [
                            'labels' => $statusPOChart->pluck('status_group'),
                            'data' => $statusPOChart->pluck('total'),
                            'colors' => $statusPOChart->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $statusPOChart->values()->map(fn($r, $i) => [
                            'label' => $r['status_group'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon' => 'clipboard-check',
                        ]),
                    ],
                    [
                        'id'    => 'alasanRetur',
                        'title' => 'Alasan Retur Supplier',
                        'chart' => [
                            'labels' => $alasanRetur->pluck('alasan'),
                            'data'   => $alasanRetur->pluck('total'),
                            'colors' => $alasanRetur->values()->map(
                                fn($_, $i) => $donutColors[$i % count($donutColors)]
                            ),
                        ],
                        'details' => $alasanRetur->values()->map(fn($r, $i) => [
                            'label' => $r['alasan'],
                            'value' => $r['total'],
                            'color' => $donutColors[$i % count($donutColors)],
                            'icon' => 'undo-alt',
                        ]),
                    ],


                ],
            ],

            ['type' => 'title', 'title' => 'Grafik'],

            [
                'type' => 'charts',
                'per_row' => 3,
                'items' => [
                    [
                        'id' => 'trendPembelian',
                        'type' => 'line',
                        'title' => 'Tren Pembelian Bulanan',
                        'data' => [
                            'labels' => $trendPembelian->pluck('bulan')->map(fn($b) => "Bulan $b"),
                            'datasets' => [
                                ['label' => 'Jumlah PO', 'data' => $trendPembelian->pluck('jumlah_po')],
                                ['label' => 'Total Transaksi', 'data' => $trendPembelian->pluck('total_nilai'), 'yAxisID' => 'y1'],
                            ],
                        ],
                    ],
                    [
                        'id' => 'permintaanCabang',
                        'type' => 'line',
                        'title' => 'Permintaan Cabang per Bulan',
                        'data' => [
                            'labels' => $labels,
                            'datasets' => $datasets,
                        ],
                    ],
                    [
                        'id' => 'statusPengiriman',
                        'type' => 'line',
                        'title' => 'Status Pengiriman',
                        'data' => [
                            'labels' => $statusPengiriman->pluck('status'),
                            'datasets' => [
                                ['label' => 'Jumlah Pengiriman', 'data' => $statusPengiriman->pluck('total')],
                            ],
                        ],
                    ],
                ],
            ],

            ['type' => 'title', 'title' => 'Peringkat Supplier'],

            [
                'type' => 'tables',
                'per_row' => 2,
                'items' => [
                    [
                        'theme' => 'primary',
                        'icon' => 'truck',
                        'title' => 'Top 5 Supplier',
                        'headers' => [
                            ['key' => 'nama_supplier', 'label' => 'Supplier'],
                            ['key' => 'total_po', 'label' => 'Total PO'],
                            ['key' => 'total_nilai', 'label' => 'Total Nilai'],
                        ],
                        'rows' => $topSuppliers->map(fn($r) => [
                            'nama_supplier' => $r->nama_supplier,
                            'total_po' => ['type' => 'badge', 'class' => 'badge-info', 'text' => $r->total_po],
                            'total_nilai' => 'Rp ' . number_format($r->total_nilai ?? 0),
                        ]),
                        'empty_text' => '',
                    ],
                    [
                        'theme' => 'warning',
                        'icon' => 'undo-alt',
                        'title' => 'Top 5 Supplier Retur',
                        'headers' => [
                            ['key' => 'nama_supplier', 'label' => 'Supplier'],
                            ['key' => 'total_retur', 'label' => 'Total Retur'],
                            ['key' => 'total_qty', 'label' => 'Total Qty'],
                        ],
                        'rows' => $supplierRetur->map(fn($r) => [
                            'nama_supplier' => $r->nama_supplier,
                            'total_retur' => ['type' => 'badge', 'class' => 'badge-warning', 'text' => $r->total_retur],
                            'total_qty' => ['type' => 'badge', 'class' => 'badge-danger', 'text' => $r->total_qty],
                        ]),
                        'empty_text' => '',
                    ],
                ],
            ],
        ]);
    }
}
