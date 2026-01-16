@extends('layouts.app')
@section('titlePage', 'Laporan Retur')

@section('app')
    <div class="row">
        <div class="col-12">

            {{-- FILTER --}}
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row">

                        <div class="col-md-2">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Kode PO / Supplier / Alasan"
                                value="{{ request('search') }}">
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
                            <label>Status Retur</label>
                            <select name="status_retur" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach (['Menunggu', 'Diproses', 'Selesai', 'Ditolak'] as $status)
                                    <option value="{{ $status }}"
                                        {{ request('status_retur') == $status ? 'selected' : '' }}>
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
                    <a href="{{ route('laporan.retur.pdf', request()->query()) }}" class="btn btn-danger ml-auto">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode PO</th>
                                <th>Supplier</th>
                                <th>Tanggal Retur</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($returs as $retur)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $retur->purchaseOrder->kode_po ?? '-' }}</td>
                                    <td>{{ $retur->purchaseOrder->supplier->nama_supplier ?? '-' }}</td>
                                    <td>{{ $retur->tanggal_retur }}</td>
                                    <td class="text-center">{{ $retur->qty_retur }}</td>
                                    <td>{{ $retur->status_retur }}</td>
                                    <td>{{ $retur->alasan }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $returs->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection
