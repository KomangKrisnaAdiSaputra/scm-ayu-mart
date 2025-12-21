@extends('layouts.app')
@section('titlePage', 'Manajemen Produk')

@php
    $roleManajer = auth()->user()->role == 'Manajer';
@endphp
@section('app')
    {{-- <h2 class="section-title">Table</h2>
    <p class="section-lead">Example of some Bootstrap table components.</p> --}}
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('produk.form') }}"" class="btn btn-primary">Tambah Data</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Produk</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('produk') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search produk..."
                                    value="{{ request('search') }}">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary">
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
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Kategori / Satuan</th>
                                @if ($roleManajer)
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                @else
                                    <th>Harga</th>
                                @endif
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            @foreach ($produk as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->kode_produk }}</td>
                                    <td>{{ $item->nama_produk }}</td>
                                    <td>{{ $item->kategori }} / {{ $item->satuan }}</td>
                                    @if ($roleManajer)
                                        <td>{{ $item->harga_beli }}</td>
                                    @endif
                                    <td>{{ $item->harga_jual }}</td>
                                    <td>
                                        <div
                                            class="badge  {{ $item->status_produk == 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $item->status_produk }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($roleManajer)
                                            <a href="{{ route('produk.form', ['id' => $item->produk_id]) }}"
                                                class="btn btn-secondary btn-sm mr-1">
                                                Edit
                                            </a>

                                            <form action="{{ route('produk.delete', $item->produk_id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirmDelete()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
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

@section('js')
    <script>
        function confirmDelete() {
            if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                return false;
            }

            return confirm('Data yang dihapus TIDAK BISA dikembalikan. Lanjutkan?');
        }
    </script>

@endsection
