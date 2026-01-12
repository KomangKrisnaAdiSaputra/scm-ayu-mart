{{-- <h1>Dashboard Manajer</h1>

<p>Total PO: {{ $totalPO }}</p>
<p>Total Produk: {{ $totalProduk }}</p>
<p>Total Supplier: {{ $totalSupplier }}</p> --}}


@extends('layouts.app')
@section('titlePage', 'Dashboard')
@section('app')
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="far fa-user"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Produk</h4>
                    </div>
                    <div class="card-body">
                        10
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="far fa-newspaper"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Pembelian perbulan</h4>
                    </div>
                    <div class="card-body">
                        42
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="far fa-file"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Nilai stok saat ini</h4>
                    </div>
                    <div class="card-body">
                        1,201
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-circle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Cabang aktif</h4>
                    </div>
                    <div class="card-body">
                        47
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        @if (in_array(auth()->user()->role, ['Gudang']))
            {{-- STOK MENIPIS --}}
            <div class="col-xl-4 col-lg-6 col-md-12 mb-4">
                <div class="card border-danger shadow h-100">
                    <div class="card-header bg-danger text-white d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <h5 class="mb-0">Stok Produk Menipis</h5>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th width="180">Kondisi Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produkGudang as $item)
                                    @php
                                        $dataStokGudang = $stokGudang->where('produk_id', $item->id_produk)->first();
                                        $totalGudang = $dataStokGudang->stok_total;
                                        $minGudang = $dataStokGudang->stok_minimum;
                                        $persenGudang = $minGudang > 0 ? ($totalGudang / $minGudang) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $item->nama_produk }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $item->kategori }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger mb-1">
                                                🔴 {{ $totalGudang }} / {{ $minGudang }}
                                            </span>

                                            <div class="progress" style="height:6px;">
                                                <div class="progress-bar bg-danger"
                                                    style="width: {{ min(100, $persenGudang) }}%">
                                                </div>
                                            </div>

                                            <small class="text-danger font-weight-bold">
                                                Stok menipis
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($produkMenipis->isEmpty())
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle text-success"></i>
                                            Semua stok aman
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array(auth()->user()->role, ['Cabang']))
            {{-- STOK MENIPIS --}}
            <div class="col-xl-4 col-lg-6 col-md-12 mb-4">
                <div class="card border-danger shadow h-100">
                    <div class="card-header bg-danger text-white d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <h5 class="mb-0">Stok Produk Menipis</h5>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th width="180">Kondisi Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produkCabang as $item)
                                    @php
                                        $dataStokCabang = $stokCabang->where('id_produk', $item->id_produk)->first();
                                        $totalCabang = $dataStokCabang->total_stok;
                                        $minCabang = $dataStokCabang->stok_minimum;
                                        $persenCabang = $minCabang > 0 ? ($totalCabang / $minCabang) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $item->nama_produk }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $item->kategori }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger mb-1">
                                                🔴 {{ $totalCabang }} / {{ $minCabang }}
                                            </span>

                                            <div class="progress" style="height:6px;">
                                                <div class="progress-bar bg-danger"
                                                    style="width: {{ min(100, $persenCabang) }}%">
                                                </div>
                                            </div>

                                            <small class="text-danger font-weight-bold">
                                                Stok menipis
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($produkMenipis->isEmpty())
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle text-success"></i>
                                            Semua stok aman
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- <div class="col-xl-4 col-lg-6 col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h5 class="mb-0">Permintaan Cabang</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-primary">12</h2>
                    <small class="text-muted">Menunggu diproses</small>
                </div>
            </div>
        </div> --}}

    </div>


@endsection
