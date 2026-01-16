@extends('layouts.app')
@section('titlePage', 'Laporan Purchase Order')

@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            {{-- FILTER --}}
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-2">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari kode PO / supplier / status" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label>Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control"
                                value="{{ request('tanggal_awal') }}">
                        </div>

                        <div class="col-md-2">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control"
                                value="{{ request('tanggal_akhir') }}">
                        </div>

                        <div class="col-md-2">
                            <label>Supplier</label>
                            <select name="supplier_id" class="form-control">
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}"
                                        {{ request('supplier_id') == $supplier->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Status PO</label>
                            <select name="status_po" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach (['Menunggu Persetujuan', 'Disetujui Manajer', 'Dikirim Supplier', 'Retur', 'Selesai'] as $status)
                                    <option value="{{ $status }}"
                                        {{ request('status_po') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('laporan.purchaseorder.pdf', request()->query()) }}" class="btn btn-danger ml-auto">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode PO</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchaseOrders as $po)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $po->kode_po }}</td>
                                    <td>{{ $po->tanggal_po }}</td>
                                    <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($po->total_po) }}</td>
                                    <td>{{ $po->status_po }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
