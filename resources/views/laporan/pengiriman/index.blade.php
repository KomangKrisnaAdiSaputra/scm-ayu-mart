@extends('layouts.app')
@section('titlePage', 'Laporan Pengiriman')

@section('app')
    <div class="row">
        <div class="col-12">

            {{-- FILTER --}}
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row">

                        <div class="col-md-3">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Kode Permintaan / Status"
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

                        <div class="col-md-3">
                            <label>Status Pengiriman</label>
                            <select name="status_pengiriman" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach (['Diproses', 'Dikirim', 'Diterima', 'Selesai', 'Gagal'] as $status)
                                    <option value="{{ $status }}"
                                        {{ request('status_pengiriman') == $status ? 'selected' : '' }}>
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
                    <a href="{{ route('laporan.pengiriman.pdf', request()->query()) }}" class="btn btn-danger ml-auto">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Permintaan</th>
                                <th>Cabang</th>
                                <th>Tanggal Kirim</th>
                                <th>Status</th>
                                <th>Kurir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengiriman as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->permintaan->kode_permintaan ?? '-' }}</td>
                                    <td>{{ $item->permintaan->cabang->nama_cabang ?? '-' }}</td>
                                    <td>{{ $item->tanggal_kirim }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $item->status_pengiriman }}
                                        </span>
                                    </td>
                                    <td>{{ $item->status_kurir->nama_kurir ?? '-' }}</td>
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

                    {{ $pengiriman->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection
