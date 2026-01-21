@extends('layouts.app')
@section('titlePage', 'Laporan')

@section('app')
    <div class="container-fluid">

        {{-- ================= FILTER ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Filter Laporan</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label>Dari Tanggal</label>
                                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label>Sampai</label>
                                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label>Status PO</label>
                                    <select name="status_po" class="form-control">
                                        <option value="">Semua</option>
                                        @foreach (['Disetujui Manajer', 'Dikirim Supplier', 'Selesai', 'Retur'] as $s)
                                            <option value="{{ $s }}" @selected(request('status_po') == $s)>
                                                {{ $s }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label>Status Pengiriman</label>
                                    <select name="status_pengiriman" class="form-control">
                                        <option value="">Semua</option>
                                        @foreach (['Dikirim', 'Diterima', 'Pending'] as $s)
                                            <option value="{{ $s }}" @selected(request('status_pengiriman') == $s)>
                                                {{ $s }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>

                                <div class="col-md-2">
                                    <a href="{{ route('laporan.pdf', request()->query()) }}" target="_blank"
                                        class="btn btn-danger w-100">
                                        <i class="fas fa-file-pdf"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= LAPORAN PO ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Purchase Order</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode PO</th>
                                        <th>Tanggal</th>
                                        <th>Supplier</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($laporanPO as $i => $po)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $po->kode_po }}</td>
                                            <td>{{ $po->tanggal_po }}</td>
                                            <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $po->status_po }}</span>
                                            </td>
                                            <td align="right">
                                                <strong>Rp {{ number_format($po->total_po, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Data tidak tersedia</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">Total Pengeluaran</td>
                                        <td align="right">
                                            Rp {{ number_format($totalNilaiPO, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= PO SUPPLIER ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Purchase Order Supplier</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Supplier</th>
                                    <th>Jumlah PO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laporanPOSupplier as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row->nama_supplier }}</td>
                                        <td align="right">{{ $row->total_po }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= LAPORAN RETUR ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Retur</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal Retur</th>
                                        <th>Kode PO</th>
                                        <th>Supplier</th>
                                        <th>Qty Retur</th>
                                        <th>Status</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($laporanRetur as $i => $r)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $r->tanggal_retur }}</td>
                                            <td>{{ $r->purchaseOrder->kode_po ?? '-' }}</td>
                                            <td>{{ $r->purchaseOrder->supplier->nama_supplier ?? '-' }}</td>
                                            <td align="right">{{ $r->qty_retur }}</td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    {{ $r->status_retur }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $r->alasan }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                Data retur tidak tersedia
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <td colspan="4">Total Qty Retur</td>
                                        <td align="right"><strong>{{ $totalQtyRetur }}</strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- ================= RETUR SUPPLIER ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Retur Supplier</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Supplier</th>
                                    <th>Jumlah Retur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laporanReturSupplier as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row->nama_supplier }}</td>
                                        <td align="right">{{ $row->total_retur }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= PENGIRIMAN ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Pengiriman</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laporanPengiriman as $row)
                                    <tr>
                                        <td>{{ $row->status_pengiriman }}</td>
                                        <td align="right">{{ $row->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td align="right">{{ $laporanPengiriman->sum('total') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= PRODUK ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Produk</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>Jenis Produk</th>
                                    <th>Total Produk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laporanProduk as $row)
                                    <tr>
                                        <td>{{ $row->jenis->nama_jenis ?? '-' }}</td>
                                        <td align="right">{{ $row->total_produk }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= PERMINTAAN CABANG ================= --}}
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Laporan Permintaan Cabang</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>Cabang</th>
                                    <th>Jumlah Permintaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laporanPermintaanCabang as $row)
                                    <tr>
                                        <td>{{ $row->cabang_id }}</td>
                                        <td align="right">{{ $row->total_permintaan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
