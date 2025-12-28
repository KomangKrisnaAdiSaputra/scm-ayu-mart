@extends('layouts.app')
@section('titlePage', 'Retur')
@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            @if (auth()->user()->role == 'Gudang')
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('retur.create') }}"" class="btn btn-primary">Buat Retur</a>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Data Retur</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('retur') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search PO / Produk / Status..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary custom-search-button" type="submit"
                                        style="border-radius: 0 30px 30px 0 !important;margin-top: -0;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-md">
                            <tr>
                                <th>#</th>
                                <th>Kode PO</th>
                                <th>Produk</th>
                                <th>Qty Retur</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                            @forelse ($retur as $key => $item)
                                <tr>
                                    <td>{{ $retur->firstItem() + $key }}</td>
                                    <td>{{ $item->purchaseOrder->kode_po ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $item->produk->nama_produk }}</strong><br>
                                        <small class="text-muted">
                                            {{ $item->produk->kode_produk }}
                                        </small>
                                    </td>
                                    <td>{{ $item->qty_retur }}</td>
                                    <td>{{ $item->tanggal_retur }}</td>
                                    <td>
                                        <span
                                            class="badge 
                            {{ $item->status_retur == 'Diterima'
                                ? 'badge-success'
                                : ($item->status_retur == 'Ditolak'
                                    ? 'badge-danger'
                                    : 'badge-warning') }}">
                                            {{ $item->status_retur }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-info btn-sm">
                                            Detail
                                        </button>

                                        @if ($item->status_retur == 'Menunggu Konfirmasi' && auth()->user()->role == 'Supplier')
                                            <form action="{{ route('retur.terima', $item->retur_id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Terima retur ini?')">
                                                @csrf
                                                <button class="btn btn-success btn-sm">Terima</button>
                                            </form>

                                            <form action="{{ route('retur.tolak', $item->retur_id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Tolak retur ini?')">
                                                @csrf
                                                <button class="btn btn-danger btn-sm">Tolak</button>
                                            </form>
                                        @endif

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        Data retur tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                {{-- <div class="card-footer text-right">
                    <nav class="d-inline-block">
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1 <span
                                        class="sr-only">(current)</span></a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div> --}}
            </div>
        </div>
    </div>
@endsection
