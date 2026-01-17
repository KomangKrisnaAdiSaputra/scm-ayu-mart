@extends('layouts.app')
@section('titlePage', 'Laporan Stok Gudang')

@section('app')
    <div class="row">
        <div class="col-12">

            {{-- FILTER --}}
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row">

                        <div class="col-md-3">
                            <label>Search Produk</label>
                            <input type="text" name="search" class="form-control" placeholder="Kode / Nama Produk"
                                value="{{ request('search') }}">
                        </div>

                        <div class="col-md-3">
                            <label>Status Stok</label>
                            <select name="status" class="form-control">
                                <option value="">Semua</option>
                                <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>
                                    Stok Menipis
                                </option>
                                <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>
                                    Stok Aman
                                </option>
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
                    <a href="{{ route('laporan.stokgudang.pdf', request()->query()) }}" class="btn btn-danger ml-auto">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Stok Total</th>
                                <th>Stok Minimum</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stok as $item)
                                @php
                                    $p = $produk[$item->produk_id] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->kode_produk ?? '-' }}</td>
                                    <td>{{ $p->nama_produk ?? '-' }}</td>
                                    <td class="text-center">{{ $item->stok_total }}</td>
                                    <td class="text-center">{{ $item->stok_minimum }}</td>
                                    <td>
                                        @if ($item->stok_total <= $item->stok_minimum)
                                            <span class="badge badge-danger">Menipis</span>
                                        @else
                                            <span class="badge badge-success">Aman</span>
                                        @endif
                                    </td>
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

                    {{ $stok->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection
