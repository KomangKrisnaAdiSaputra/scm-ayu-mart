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

        $funProps = $datas[auth()->user()->role] ?? null;
        $props = !$funProps ? [] : $this->$funProps();
        return view("dashboard", [...$props]);
    }

    function dashSupplier()
    {
        $periodeTahun = now()->year;

        // Ambil semua PO sekali saja
        $po = PurchaseOrder::all();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY PO (COLLECTION BASED)
        |--------------------------------------------------------------------------
        */
        $totalPO = $po->count();
        $poSelesai = $po->where('status_po', 'Selesai')->count();
        $totalPOBulanIni = $po->filter(fn($item) => \Carbon\Carbon::parse($item->tanggal_po)->isSameMonth(now()))->count();
        $statusPO = $po->groupBy(fn($item) => match ($item->status_po) {
            'Menunggu Persetujuan' => 'Menunggu',
            'Disetujui Manajer'    => 'Disetujui',
            'Dikirim Supplier'    => 'Dikirim',
            'Selesai'             => 'Selesai',
            default               => 'Lainnya',
        })->map(fn($items, $key) => [
            'status_group' => $key,
            'total'        => $items->count(),
        ])->values();

        /*
        |--------------------------------------------------------------------------
        | RETUR 
        |--------------------------------------------------------------------------
        */
        $retur = Retur::whereYear('tanggal_retur', $periodeTahun)->get();
        $totalRetur = $retur->count();
        $alasanRetur = $retur->groupBy('alasan')->map(fn($items, $key) => [
            'alasan' => $key,
            'total'  => $items->count(),
        ])->sortByDesc('total')->values();

        return collect([
            "cards" => collect([
                [
                    'bg'     => 'bg-primary',
                    'icon'   => 'shopping-cart',
                    'header' => 'Total PO',
                    'body'   => $totalPO,
                ],
                [
                    'bg'     => 'bg-success',
                    'icon'   => 'check-circle',
                    'header' => 'PO Selesai',
                    'body'   => $poSelesai,
                ],
                [
                    'bg'     => 'bg-info',
                    'icon'   => 'calendar',
                    'header' => 'PO Bulan Ini',
                    'body'   => $totalPOBulanIni,
                ],
                [
                    'bg'     => 'bg-danger',
                    'icon'   => 'undo',
                    'header' => 'Total Retur',
                    'body'   => $totalRetur,
                ],
            ]),
            "charts" => collect([
                [
                    'id'    => 'statusChart',
                    'type'  => 'doughnut',
                    'title' => 'Status Purchase Order',
                    'data'  => [
                        'labels' => $statusPO->pluck('status_group')->values(),
                        'datasets' => [
                            [
                                'data' => $statusPO->pluck('total')->values(),
                            ],
                        ],
                    ],
                    'options' => [
                        'responsive' => true,
                        'plugins' => [
                            'legend' => [
                                'position' => 'bottom',
                            ],
                        ],
                    ],
                ],
                [
                    'id'    => 'chartAlasanRetur',
                    'type'  => 'pie',
                    'title' => 'Alasan Retur Supplier',
                    'data'  => [
                        'labels' => $alasanRetur->pluck('alasan')->values(),
                        'datasets' => [
                            [
                                'data' => $alasanRetur->pluck('total')->values(),
                            ],
                        ],
                    ],
                    'options' => [
                        'responsive' => true,
                        'plugins' => [
                            'legend' => [
                                'position' => 'bottom',
                            ],
                        ],
                    ],
                ],
            ])
        ]);
    }

    function dashKurir()
    {
        $pengiriman = Pengiriman::all();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */
        $totalPengiriman = $pengiriman->count();

        $totalPengirimanBulanIni = $pengiriman
            ->filter(
                fn($item) =>
                Carbon::parse($item->tanggal_kirim)->isSameMonth(now())
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | STATUS PENGIRIMAN
        |--------------------------------------------------------------------------
        */
        $pengirimanStatus = $pengiriman
            ->groupBy('status_pengiriman')
            ->map(fn($items) => $items->count());

        /*
        |--------------------------------------------------------------------------
        | STATUS CARD CONFIG
        |--------------------------------------------------------------------------
        */
        $statusPengirimanConfig = [
            'Diproses' => ['bg' => 'bg-warning', 'icon' => 'cogs'],
            'Dikirim'  => ['bg' => 'bg-info',    'icon' => 'truck'],
            'Diterima' => ['bg' => 'bg-success', 'icon' => 'box-open'],
            'Selesai'  => ['bg' => 'bg-primary', 'icon' => 'check-circle'],
            'Gagal'    => ['bg' => 'bg-danger',  'icon' => 'times-circle'],
        ];

        /*
        |--------------------------------------------------------------------------
        | BUILD STATUS CARDS
        |--------------------------------------------------------------------------
        */
        $cardsStatusPengiriman = collect($statusPengirimanConfig)
            ->map(function ($config, $status) use ($pengirimanStatus) {
                return [
                    'bg'     => $config['bg'],
                    'icon'   => $config['icon'],
                    'header' => 'Pengiriman ' . $status,
                    'body'   => $pengirimanStatus->get($status, 0),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | GRAFIK PENGIRIMAN
        |--------------------------------------------------------------------------
        */
        $grafikPengiriman = collect([
            'labels' => array_keys($statusPengirimanConfig),
            'data'   => array_values(
                collect($statusPengirimanConfig)
                    ->keys()
                    ->map(fn($status) => $pengirimanStatus->get($status, 0))
                    ->toArray()
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD DATA
        |--------------------------------------------------------------------------
        */
        return collect([
            'cards' => collect([
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
            ])->merge($cardsStatusPengiriman),

            'charts' => collect([
                [
                    'id'    => 'chartPengiriman',
                    'type'  => 'bar',
                    'title' => 'Status Pengiriman',
                    'data'  => [
                        'labels' => $grafikPengiriman['labels'],
                        'datasets' => [
                            [
                                'label' => 'Jumlah Pengiriman',
                                'data'  => $grafikPengiriman['data'],
                            ],
                        ],
                    ],
                    'options' => [
                        'indexAxis' => 'y',
                        'responsive' => true,
                        'plugins' => [
                            'legend' => [
                                'display' => false,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    function dashCabang()
    {
        /*
        |--------------------------------------------------------------------------
        | USER & CABANG
        |--------------------------------------------------------------------------
        */
        $user = auth()->user();

        $cabang = TbCabang::where('users_id', $user->users_id)->first();

        /*
        |--------------------------------------------------------------------------
        | PERMINTAAN CABANG
        |--------------------------------------------------------------------------
        */
        $permintaanCabang = PermintaanCabang::when(
            $user->role === 'Cabang',
            fn($q) => $q->where('cabang_id', $cabang?->id_cabang)
        )->get();
        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */
        $totalPermintaan = $permintaanCabang->count();

        $totalPermintaanBulanIni = $permintaanCabang
            ->filter(
                fn($item) =>
                Carbon::parse($item->tanggal_permintaan)->isSameMonth(now())
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | STATUS PERMINTAAN
        |--------------------------------------------------------------------------
        */
        $permintaanStatus = $permintaanCabang
            ->groupBy('status_permintaan')
            ->map(fn($items) => $items->count());

        /*
        |--------------------------------------------------------------------------
        | STATUS CARD CONFIG
        |--------------------------------------------------------------------------
        */
        $statusPermintaanConfig = [
            'Menunggu' => ['bg' => 'bg-warning', 'icon' => 'clock'],
            'Diterima' => ['bg' => 'bg-success', 'icon' => 'check-circle'],
            'Ditolak'  => ['bg' => 'bg-danger',  'icon' => 'times-circle'],
        ];

        /*
        |--------------------------------------------------------------------------
        | BUILD STATUS CARDS
        |--------------------------------------------------------------------------
        */
        $cardsStatusPermintaan = collect($statusPermintaanConfig)
            ->map(function ($config, $status) use ($permintaanStatus) {
                return [
                    'bg'     => $config['bg'],
                    'icon'   => $config['icon'],
                    'header' => 'Permintaan ' . $status,
                    'body'   => $permintaanStatus->get($status, 0),
                ];
            })->merge([
                [
                    'bg'     => '',
                    'icon'   => '',
                    'header' => '',
                    'body'   => "",
                    'hidden' => ""
                ]
            ])->values();

        $stokCabang = TbStokCabang::whereColumn('total_stok', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkCabang = TbProduk::whereIn(
            'id_produk',
            $stokCabang->pluck('id_produk')
        )->get();

        $tabelStokMenipis = $stokCabang
            ->map(function ($stok) use ($produkCabang) {
                $produk = $produkCabang->firstWhere('id_produk', $stok->id_produk);

                return [
                    'nama_produk'  => $produk?->nama_produk ?? '-',
                    'total_stok'   => $stok->total_stok,
                    'stok_minimum' => $stok->stok_minimum,
                    'status'       => $stok->total_stok <= $stok->stok_minimum,
                ];
            })
            ->values();

        return collect([
            'cards' => collect([
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
            ])->merge($cardsStatusPermintaan),
            'tables' => collect([
                [
                    'id'    => 'stokMenipis',
                    'col'   => 6,
                    'theme' => 'danger',
                    'icon'  => 'exclamation-triangle',
                    'title' => 'Stok Cabang Menipis',
                    'headers' => [
                        ['key' => 'nama_produk', 'label' => 'Produk'],
                        ['key' => 'stok', 'label' => 'Stok'],
                    ],
                    'rows' => $tabelStokMenipis->map(fn($row) => [
                        'nama_produk' => $row['nama_produk'],
                        'stok' => [
                            'type'  => 'badge',
                            'class' => 'badge-danger',
                            'text'  => "{$row['total_stok']} / {$row['stok_minimum']}",
                        ],
                    ]),
                    'empty_text' => 'Semua stok aman',
                ],
            ])
        ]);
    }

    function dashManajer()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA SEKALI
        |--------------------------------------------------------------------------
        */
        $purchaseOrders = PurchaseOrder::all();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY PO
        |--------------------------------------------------------------------------
        */
        $totalPO = $purchaseOrders->count();

        $poBulanIni = $purchaseOrders->filter(
            fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now())
        );

        /*
        |--------------------------------------------------------------------------
        | STATUS PO BULAN INI (UNTUK CARD)
        |--------------------------------------------------------------------------
        */
        $statusPOBulanIni = $poBulanIni
            ->groupBy('status_po')
            ->map(fn($items) => $items->count());

        /*
        |--------------------------------------------------------------------------
        | CONFIG CARD STATUS PO
        |--------------------------------------------------------------------------
        */
        $statusPOConfig = [
            'Menunggu Persetujuan' => ['bg' => 'bg-warning', 'icon' => 'cogs'],
            'Disetujui Manajer'    => ['bg' => 'bg-info',    'icon' => 'truck'],
            'Dikirim Supplier'    => ['bg' => 'bg-success', 'icon' => 'box-open'],
            'Selesai'             => ['bg' => 'bg-primary', 'icon' => 'check-circle'],
        ];

        /*
        |--------------------------------------------------------------------------
        | CARD STATUS PO (DINAMIS)
        |--------------------------------------------------------------------------
        */
        $cardsStatusPO = collect($statusPOConfig)
            ->map(function ($config, $status) use ($statusPOBulanIni) {
                return [
                    'bg'     => $config['bg'],
                    'icon'   => $config['icon'],
                    'header' => $status,
                    'body'   => $statusPOBulanIni->get($status, 0),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY LAIN
        |--------------------------------------------------------------------------
        */
        $totalPengeluaran = InvoicePayment::whereHas(
            'invoice',
            fn($q) => $q->where('status_invoice', 'Lunas')
        )->sum('jumlah_bayar');

        $totalRetur    = Retur::count();
        $totalSupplier = Supplier::count();
        $totalCabang   = TbCabang::count();

        /*
        |--------------------------------------------------------------------------
        | CHART: TREN PEMBELIAN BULANAN
        |--------------------------------------------------------------------------
        */
        $trendPembelian = $purchaseOrders
            ->filter(
                fn($po) =>
                Carbon::parse($po->tanggal_po)->year === now()->year
            )
            ->groupBy(
                fn($po) =>
                Carbon::parse($po->tanggal_po)->month
            )
            ->map(function ($items, $bulan) {
                return [
                    'bulan'        => $bulan,
                    'jumlah_po'    => $items->count(),
                    'total_nilai'  => $items->sum('total_po'),
                ];
            })
            ->sortBy('bulan')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | CHART: STATUS PO (GROUP)
        |--------------------------------------------------------------------------
        */
        $statusPOChart = $purchaseOrders
            ->groupBy(function ($po) {
                return match ($po->status_po) {
                    'Menunggu Persetujuan' => 'Menunggu',
                    'Disetujui Manajer'    => 'Disetujui',
                    'Dikirim Supplier'    => 'Dikirim',
                    'Selesai'             => 'Selesai',
                    default               => 'Lainnya',
                };
            })
            ->map(fn($items) => $items->count())
            ->map(fn($total, $status) => [
                'status_group' => $status,
                'total'        => $total,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD DATA
        |--------------------------------------------------------------------------
        */

        return collect([
            'cards' => collect([
                [
                    'bg'     => 'bg-primary',
                    'icon'   => 'shopping-cart',
                    'header' => 'Total Purchase Order',
                    'body'   => $totalPO,
                ],
                [
                    'bg'     => 'bg-info',
                    'icon'   => 'calendar',
                    'header' => 'PO Bulan Ini',
                    'body'   => $poBulanIni->count(),
                ],
                [
                    'bg'     => 'bg-success',
                    'icon'   => 'money-bill-wave',
                    'header' => 'Total Pengeluaran',
                    'body'   => number_format($totalPengeluaran),
                ],
                [
                    'bg'     => 'bg-warning',
                    'icon'   => 'undo',
                    'header' => 'Total Retur',
                    'body'   => $totalRetur,
                ],
                [
                    'bg'     => 'bg-dark',
                    'icon'   => 'truck-loading',
                    'header' => 'Total Supplier',
                    'body'   => $totalSupplier,
                ],
                [
                    'bg'     => 'bg-secondary',
                    'icon'   => 'store',
                    'header' => 'Total Cabang',
                    'body'   => $totalCabang,
                ],
            ])->merge($cardsStatusPO),

            'charts' => collect([
                [
                    'id'    => 'trendPembelian',
                    'type'  => 'line',
                    'title' => 'Tren Pembelian Bulanan',
                    'data'  => [
                        'labels' => $trendPembelian
                            ->pluck('bulan')
                            ->map(fn($b) => "Bulan $b"),

                        'datasets' => [
                            [
                                'label' => 'Jumlah PO',
                                'data'  => $trendPembelian->pluck('jumlah_po'),
                            ],
                            [
                                'label'    => 'Total Transaksi',
                                'data'     => $trendPembelian->pluck('total_nilai'),
                                'tension'  => 0.4,
                                'yAxisID'  => 'y1',
                            ],
                        ],
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true,
                            ],
                            'y1' => [
                                'beginAtZero' => true,
                                'position'    => 'right',
                            ],
                        ],
                    ],
                ],
                [
                    'id'    => 'statusPO',
                    'type'  => 'doughnut',
                    'title' => 'Status Purchase Order',
                    'data'  => [
                        'labels' => $statusPOChart->pluck('status_group'),
                        'datasets' => [
                            [
                                'data' => $statusPOChart->pluck('total'),
                            ],
                        ],
                    ],
                    'options' => (object) [],
                ],
            ]),
        ]);
    }

    function dashGudang()
    {
        $periodeTahun = now()->year;

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA SEKALI
        |--------------------------------------------------------------------------
        */
        $purchaseOrders   = PurchaseOrder::all();
        $permintaanCabang = PermintaanCabang::all();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY PO
        |--------------------------------------------------------------------------
        */
        $totalPO = $purchaseOrders->count();

        $poBulanIni = $purchaseOrders->filter(
            fn($po) => Carbon::parse($po->tanggal_po)->isSameMonth(now())
        );

        /*
        |--------------------------------------------------------------------------
        | STATUS PO BULAN INI (CARD)
        |--------------------------------------------------------------------------
        */
        $statusPOBulanIni = $poBulanIni
            ->groupBy('status_po')
            ->map(fn($items) => $items->count());

        $statusPOConfig = [
            'Menunggu Persetujuan' => ['bg' => 'bg-warning', 'icon' => 'cogs'],
            'Disetujui Manajer'    => ['bg' => 'bg-info',    'icon' => 'truck'],
            'Dikirim Supplier'    => ['bg' => 'bg-success', 'icon' => 'box-open'],
            'Selesai'             => ['bg' => 'bg-primary', 'icon' => 'check-circle'],
        ];

        $cardsStatusPO = collect($statusPOConfig)
            ->map(fn($config, $status) => [
                'bg'     => $config['bg'],
                'icon'   => $config['icon'],
                'header' => $status,
                'body'   => $statusPOBulanIni->get($status, 0),
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RETUR & CABANG
        |--------------------------------------------------------------------------
        */
        $retur        = Retur::whereYear('tanggal_retur', $periodeTahun)->get();
        $totalRetur  = $retur->count();
        $totalCabang = TbCabang::count();

        /*
        |--------------------------------------------------------------------------
        | PERMINTAAN CABANG BULAN INI
        |--------------------------------------------------------------------------
        */
        $totalPermintaanBulanIni = $permintaanCabang
            ->filter(
                fn($item) =>
                Carbon::parse($item->tanggal_permintaan)->isSameMonth(now())
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | DISTRIBUSI STOK GUDANG PER JENIS
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | CHART: STATUS PO
        |--------------------------------------------------------------------------
        */
        $statusPOChart = $purchaseOrders
            ->groupBy(fn($po) => match ($po->status_po) {
                'Menunggu Persetujuan' => 'Menunggu',
                'Disetujui Manajer'    => 'Disetujui',
                'Dikirim Supplier'    => 'Dikirim',
                'Selesai'             => 'Selesai',
                default               => 'Lainnya',
            })
            ->map(fn($items) => $items->count())
            ->map(fn($total, $status) => [
                'status_group' => $status,
                'total'        => $total,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | STOK GUDANG MENIPIS
        |--------------------------------------------------------------------------
        */
        $stokGudang = StokGudang::whereColumn('stok_total', '<=', 'stok_minimum')
            ->limit(5)
            ->get();

        $produkGudang = TbProduk::whereIn(
            'id_produk',
            $stokGudang->pluck('produk_id')
        )->get();

        /*
        |--------------------------------------------------------------------------
        | MAPPING TABEL STOK GUDANG
        |--------------------------------------------------------------------------
        */
        $tabelStokGudangMenipis = $stokGudang
            ->map(function ($stok) use ($produkGudang) {
                $produk = $produkGudang->firstWhere('id_produk', $stok->produk_id);

                return [
                    'nama_produk'  => $produk?->nama_produk ?? '-',
                    'total_stok'   => $stok->stok_total,
                    'stok_minimum' => $stok->stok_minimum,
                    'status'       => $stok->stok_total <= $stok->stok_minimum,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD CONFIG
        |--------------------------------------------------------------------------
        */
        return collect([
            'cards' => collect([
                [
                    'bg'     => 'bg-primary',
                    'icon'   => 'shopping-cart',
                    'header' => 'Total Purchase Order',
                    'body'   => $totalPO,
                ],
                [
                    'bg'     => 'bg-info',
                    'icon'   => 'calendar',
                    'header' => 'PO Bulan Ini',
                    'body'   => $poBulanIni->count(),
                ],
                [
                    'bg'     => 'bg-warning',
                    'icon'   => 'undo',
                    'header' => 'Total Retur',
                    'body'   => $totalRetur,
                ],
                [
                    'bg'     => 'bg-secondary',
                    'icon'   => 'store',
                    'header' => 'Total Cabang',
                    'body'   => $totalCabang,
                ],
                [
                    'bg'     => 'bg-info',
                    'icon'   => 'calendar',
                    'header' => 'Permintaan Bulan Ini',
                    'body'   => $totalPermintaanBulanIni,
                ],
            ])->merge($cardsStatusPO),

            'charts' => collect([
                [
                    'id'    => 'statusPO',
                    'type'  => 'doughnut',
                    'title' => 'Status Purchase Order',
                    'data'  => [
                        'labels' => $statusPOChart->pluck('status_group'),
                        'datasets' => [[
                            'data' => $statusPOChart->pluck('total'),
                        ]],
                    ],
                    'options' => (object) [],
                ],
                [
                    'id'    => 'stokPerJenis',
                    'type'  => 'doughnut',
                    'title' => 'Jenis Stok Gudang',
                    'data'  => [
                        'labels' => $stokPerJenis->pluck('nama_jenis'),
                        'datasets' => [[
                            'data' => $stokPerJenis->pluck('total_stok'),
                        ]],
                    ],
                    'options' => (object) [],
                ],
            ]),

            'tables' => collect([
                [
                    'id'    => 'stokGudangMenipis',
                    'col'   => 6,
                    'theme' => 'danger',
                    'icon'  => 'exclamation-triangle',
                    'title' => 'Stok Gudang Menipis',
                    'headers' => [
                        ['key' => 'nama_produk', 'label' => 'Produk'],
                        ['key' => 'stok', 'label' => 'Stok'],
                    ],
                    'rows' => $tabelStokGudangMenipis->map(fn($row) => [
                        'nama_produk' => $row['nama_produk'],
                        'stok' => [
                            'type'  => 'badge',
                            'class' => 'badge-danger',
                            'text'  => "{$row['total_stok']} / {$row['stok_minimum']}",
                        ],
                    ]),
                    'empty_text' => 'Semua stok gudang aman',
                ],
            ]),
        ]);
    }

    function dashOwner()
    {
        $dashSupplier = $this->dashSupplier();
        $dashKurir = $this->dashKurir();
        $dashCabang = $this->dashCabang();
        $dashManajer = $this->dashManajer();

        $datas = collect([
            'cards' => collect([
                ...($dashSupplier["cards"] ?? []),
                ...($dashKurir["cards"] ?? []),
                ...($dashCabang["cards"] ?? []),
                ...($dashManajer["cards"] ?? []),
            ])->unique("header")->filter(fn($item) => !isset($item["hidden"]))->values(),
            'charts' => collect([
                ...($dashSupplier["charts"] ?? []),
                ...($dashKurir["charts"] ?? []),
                ...($dashCabang["charts"] ?? []),
                ...($dashManajer["charts"] ?? []),
            ])->unique("id")->values(),
            'tables' => collect([
                ...($dashSupplier["tables"] ?? []),
                ...($dashKurir["tables"] ?? []),
                ...($dashCabang["tables"] ?? []),
                ...($dashManajer["tables"] ?? []),
            ])->unique("id")->values(),
        ]);
        return $datas;
    }

    public function indexx()
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
