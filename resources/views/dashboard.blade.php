@extends('layouts.app')
@section('titlePage', 'Dashboard')

@section('app')

    {{-- ===============================
    CARD RINGKASAN
=============================== --}}
    <div class="row">

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-shopping-cart"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total PO</h4>
                        </div>
                        <div class="card-body">{{ $totalPO }}</div>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info"><i class="fas fa-calendar"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>PO Bulan Ini</h4>
                        </div>
                        <div class="card-body">{{ $totalPOBulanIni }}</div>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array(auth()->user()->role, ['Manajer']))
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Pengeluaran</h4>
                        </div>
                        <div class="card-body">
                            Rp {{ number_format($totalPengeluaranBulanIni, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
            <div class="col-lg-3 col-md-6">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>PO Selesai</h4>
                        </div>
                        <div class="card-body">{{ $poSelesai }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- KPI TOTAL RETUR --}}
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Retur ({{ now()->year }})</h4>
                    </div>
                    <div class="card-body">{{ $totalRetur }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Kurir', 'Cabang']))
        <div class="row">
            <div class="col-lg-2 col-md-4">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-check"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Selesai</h4>
                        </div>
                        <div class="card-body">{{ $pengirimanSelesai }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Diproses</h4>
                        </div>
                        <div class="card-body">{{ $pengirimanDiproses }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info"><i class="fas fa-truck"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Dikirim</h4>
                        </div>
                        <div class="card-body">{{ $pengirimanDikirim }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-box"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Diterima</h4>
                        </div>
                        <div class="card-body">{{ $pengirimanDiterima }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-times"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Gagal</h4>
                        </div>
                        <div class="card-body">{{ $pengirimanGagal }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-secondary"><i class="fas fa-hourglass-half"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Rata-rata (Hari)</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($rataWaktuPengiriman ?? 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Tren Pembelian Bulanan</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Status Purchase Order</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
        <div class="row">

            {{-- DONUT DISTRIBUSI STOK --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Distribusi Stok Gudang per Jenis Produk</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="stokJenisChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- LIST STOK RENDAH --}}
            <div class="col-lg-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-triangle"></i>
                        Produk dengan Stok Rendah
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Jenis</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($produkStokRendah as $item)
                                    <tr class="text-danger">
                                        <td>{{ $item->nama_produk }}</td>
                                        <td>{{ $item->nama_jenis }}</td>
                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $item->stok_total }} / {{ $item->stok_minimum }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-success py-3">
                                            Semua stok aman
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-boxes"></i>
                        Produk dengan Stok Tinggi
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Jenis</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($produkStokTinggi as $item)
                                    <tr class="text-success">
                                        <td>{{ $item->nama_produk }}</td>
                                        <td>{{ $item->nama_jenis }}</td>
                                        <td>
                                            <span class="badge badge-success">
                                                {{ $item->stok_total }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            Tidak ada stok berlebih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tren Permintaan Restok Cabang</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPermintaanBulanan"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card ">
                    <div class="card-header">
                        <h5 class="mb-0">Produk Paling Banyak Diminta Cabang</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Total Diminta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProdukRequest as $row)
                                    @php
                                        $produk = $produkMap[$row->produk_id] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $produk->nama_produk ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $row->total_qty }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($topProdukRequest->isEmpty())
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">
                                            Belum ada data permintaan
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Supplier dengan Transaksi Terbanyak</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Jumlah PO</th>
                                    <th>Total Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSuppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->nama_supplier }}</td>
                                        <td>{{ $supplier->total_po }}</td>
                                        <td>Rp {{ number_format($supplier->total_nilai ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">

        @if (in_array($role, ['Manajer', 'Gudang']))
            <div class="col-lg-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-triangle"></i> Stok Gudang Menipis
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            @forelse($produkGudang as $item)
                                @php $stok = $stokGudang->where('produk_id',$item->id_produk)->first(); @endphp
                                <tr>
                                    <td>{{ $item->nama_produk }}</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ $stok->stok_total }} / {{ $stok->stok_minimum }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center text-success py-3">Semua stok aman</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($role, ['Manajer', 'Cabang']))
            <div class="col-lg-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-triangle"></i> Stok Cabang Menipis
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            @forelse($produkCabang as $item)
                                @php $stok = $stokCabang->where('id_produk',$item->id_produk)->first(); @endphp
                                <tr>
                                    <td>{{ $item->nama_produk }}</td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ $stok->total_stok }} / {{ $stok->stok_minimum }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center text-success py-3">Semua stok aman</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>Distribusi Status Pengiriman</h4>
                </div>
                <div class="card-body">
                    <canvas id="chartPengiriman"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
        <div class="row">

            {{-- PIE ALASAN RETUR --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Alasan Retur Supplier</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="chartAlasanRetur"></canvas>
                    </div>
                </div>
            </div>

            {{-- SUPPLIER RETUR TERBANYAK --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Supplier dengan Retur Terbanyak</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Total Retur</th>
                                    <th>Total Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($supplierRetur as $row)
                                    <tr>
                                        <td>{{ $row->nama_supplier }}</td>
                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $row->total_retur }}
                                            </span>
                                        </td>
                                        <td>{{ $row->total_qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Belum ada data retur
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- TABEL RETUR --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exchange-alt"></i>
                        Riwayat Retur Supplier
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Refund</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tabelRetur as $retur)
                                    <tr>
                                        <td>{{ $retur->tanggal_retur }}</td>
                                        <td>{{ $retur->purchaseOrder->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $retur->produk_id }}</td>
                                        <td>{{ $retur->qty_retur }}</td>
                                        <td>{{ $retur->alasan }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $retur->status_retur }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($retur->tb_payment)
                                                <span class="badge badge-success">
                                                    Rp {{ number_format($retur->tb_payment->jumlah, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Belum</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Tidak ada data retur
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        const bulan = @json($trendPembelian->pluck('bulan'));
        const jumlahPO = @json($trendPembelian->pluck('jumlah_po'));
        const totalNilai = @json($trendPembelian->pluck('total_nilai'));

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
            new Chart(trendChart, {
                type: 'line',
                data: {
                    labels: bulan.map(b => 'Bulan ' + b),
                    datasets: [{
                            label: 'Jumlah PO',
                            data: jumlahPO,
                            tension: 0.4
                        },
                        {
                            label: 'Total Transaksi',
                            data: totalNilai,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right'
                        }
                    }
                }
            });
        @endif
        new Chart(statusChart, {
            type: 'doughnut',
            data: {
                labels: @json($statusPO->pluck('status_group')),
                datasets: [{
                    data: @json($statusPO->pluck('total'))
                }]
            }
        });
    </script>

    <script>
        const jenisLabels = @json($stokPerJenis->pluck('nama_jenis'));
        const stokData = @json($stokPerJenis->pluck('total_stok'));

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
            new Chart(stokJenisChart, {
                type: 'doughnut',
                data: {
                    labels: jenisLabels,
                    datasets: [{
                        data: stokData
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        @endif
    </script>

    <script>
        const ctxPermintaan = document.getElementById('chartPermintaanBulanan');

        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
            new Chart(ctxPermintaan, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($permintaanBulanan->pluck('bulan')) !!},
                    datasets: [{
                        label: 'Total Permintaan',
                        data: {!! json_encode($permintaanBulanan->pluck('total_qty')) !!},
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        @endif
    </script>

    <script>
        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Kurir', 'Cabang']))
            new Chart(document.getElementById('chartPengiriman'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($grafikPengiriman->keys()) !!},
                    datasets: [{
                        label: 'Jumlah Pengiriman',
                        data: {!! json_encode($grafikPengiriman->values()) !!}
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        @endif
    </script>

    <script>
        @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
            new Chart(document.getElementById('chartAlasanRetur'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode($alasanRetur->pluck('alasan')) !!},
                    datasets: [{
                        data: {!! json_encode($alasanRetur->pluck('total')) !!}
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        @endif
    </script>

@endsection
