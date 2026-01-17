@extends('layouts.app')
@section('titlePage', 'Laporan Stok Cabang')

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
                            <label>Cabang</label>
                            <select name="id_cabang" class="form-control">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabangs as $cabang)
                                    <option value="{{ $cabang->id_cabang }}"
                                        {{ request('id_cabang') == $cabang->id_cabang ? 'selected' : '' }}>
                                        {{ $cabang->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
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
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('laporan.stokcabang.pdf', request()->query()) }}" class="btn btn-danger ml-auto">
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
                                <th>Cabang</th>
                                <th>Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stokCabang as $row)
                                @php
                                    $p = $produk[$row->id_produk] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->kode_produk ?? '-' }}</td>
                                    <td>{{ $p->nama_produk ?? '-' }}</td>
                                    <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ $row->total_stok }} / {{ $row->stok_minimum }}
                                    </td>
                                    <td>
                                        @if ($row->total_stok <= $row->stok_minimum)
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

                    {{ $stokCabang->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection
