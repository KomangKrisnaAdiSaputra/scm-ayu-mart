@extends('layouts.app')
@section('titlePage', 'Manajemen Produk')

@php
    $roleManajer = auth()->user()->role == 'Manajer';
@endphp

@section('css')
    @if (!$roleManajer)
        <style>
            .harga-beli {
                display: none;
            }
        </style>
    @endif

@endsection

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
                                <input type="text" name="search" class="form-control" placeholder="Search produk...">
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
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Kategori / Satuan</th>
                                @if ($roleManajer)
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                @else
                                    <th>Harga</th>
                                @endif
                                @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
                                    <th>Stok</th>
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
                                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
                                        <td>
                                            @php
                                                $stokTotal = $item->stok->stok_total ?? 0;
                                                $stokMinimum = $item->stok->stok_minimum ?? 0;
                                            @endphp

                                            @if ($stokTotal < $stokMinimum)
                                                <div class="text-danger font-weight-bold">
                                                    🔴 {{ $stokTotal }} / {{ $stokMinimum }}
                                                </div>
                                                <small class="text-danger">
                                                    Stok menipis
                                                </small>
                                            @else
                                                <div class="text-success font-weight-bold">
                                                    🟢 {{ $stokTotal }}
                                                </div>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        <div
                                            class="badge  {{ $item->status_produk == 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $item->status_produk }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-info btn-sm mr-1" data-toggle="modal"
                                            data-target="#modalDetailProduk" data-kode="{{ $item->kode_produk }}"
                                            data-nama="{{ $item->nama_produk }}" data-kategori="{{ $item->kategori }}"
                                            data-satuan="{{ $item->satuan }}" data-harga-beli="{{ $item->harga_beli }}"
                                            data-harga-jual="{{ $item->harga_jual }}"
                                            data-status="{{ $item->status_produk }}"
                                            data-stok-total="{{ $item->stok->stok_total ?? 0 }}"
                                            data-stok-minimum="{{ $item->stok->stok_minimum ?? 0 }}">
                                            Detail
                                        </button>


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

        $('#modalDetailProduk').on('show.bs.modal', function(event) {
            let b = $(event.relatedTarget)

            let kode = b.data('kode')
            let nama = b.data('nama')
            let kategori = b.data('kategori')
            let satuan = b.data('satuan')
            let hargaBeli = b.data('harga-beli')
            let hargaJual = b.data('harga-jual')
            let status = b.data('status')

            let stokTotal = b.data('stok-total')
            let stokMinimum = b.data('stok-minimum')

            $('#d_kode').text(kode)
            $('#d_nama').text(nama)
            $('#d_kategori').text(kategori)
            $('#d_satuan').text(satuan)

            $('#d_harga_beli').text('Rp ' + Number(hargaBeli).toLocaleString('id-ID'))
            $('#d_harga_jual').text('Rp ' + Number(hargaJual).toLocaleString('id-ID'))

            // Status badge
            let badge = $('#d_status')
            badge
                .text(status)
                .removeClass('badge-success badge-danger')
                .addClass(status === 'aktif' ? 'badge-success' : 'badge-danger')

            // Stok
            $('#d_stok_total').text(stokTotal)
            $('#d_stok_minimum').text(stokMinimum)

            // Warning jika stok di bawah minimum
            if (stokTotal <= stokMinimum) {
                $('#d_stok_total').addClass('text-danger font-weight-bold')
            } else {
                $('#d_stok_total').removeClass('text-danger font-weight-bold')
            }
        })
    </script>

@endsection


{{-- Modal Detail Produk --}}
<div class="modal fade" id="modalDetailProduk" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Produk</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <table class="table table-sm table-borderless">

                    <tr>
                        <th width="40%">Kode Produk</th>
                        <td id="d_kode"></td>
                    </tr>
                    <tr>
                        <th>Nama Produk</th>
                        <td id="d_nama"></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td id="d_kategori"></td>
                    </tr>
                    <tr>
                        <th>Satuan</th>
                        <td id="d_satuan"></td>
                    </tr>

                    <tr class="harga-beli">
                        <th>Harga Beli</th>
                        <td id="d_harga_beli"></td>
                    </tr>
                    <tr>
                        <th>Harga{{ $roleManajer ? ' Jual' : '' }}</th>
                        <td id="d_harga_jual"></td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td><span id="d_status" class="badge"></span></td>
                    </tr>

                    <tr class="table-secondary">
                        <th colspan="2">Informasi Stok</th>
                    </tr>
                    <tr>
                        <th>Stok Total</th>
                        <td id="d_stok_total"></td>
                    </tr>
                    <tr>
                        <th>Stok Minimum</th>
                        <td id="d_stok_minimum"></td>
                    </tr>

                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
