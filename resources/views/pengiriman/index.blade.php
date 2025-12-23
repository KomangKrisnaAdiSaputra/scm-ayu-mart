@extends('layouts.app')
@section('titlePage', 'Pengiriman')
@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>Data Pengiriman</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('pengiriman') }}" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cari ID / Status / Tanggal" value="{{ $search ?? '' }}">
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
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cabang</th>
                                    <th>Tanggal Kirim</th>
                                    <th>Jumlah Produk</th>
                                    <th>Total Qty</th>
                                    <th>Status Pengiriman</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($pengiriman as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ $item->permintaan->cabang->nama_cabang ?? '-' }}
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($item->tanggal_kirim)->format('d-m-Y') }}
                                        </td>

                                        {{-- jumlah jenis produk --}}
                                        <td>
                                            {{ $item->permintaan->detail->count() }}
                                        </td>

                                        {{-- total qty --}}
                                        <td>
                                            {{ $item->permintaan->detail->sum('qty_permintaan') }}
                                        </td>

                                        <td>
                                            <span
                                                class="badge badge-{{ $item->status_pengiriman == 'Diproses'
                                                    ? 'warning'
                                                    : ($item->status_pengiriman == 'Dikirim'
                                                        ? 'info'
                                                        : ($item->status_pengiriman == 'Selesai'
                                                            ? 'success'
                                                            : 'danger')) }}">
                                                {{ $item->status_pengiriman }}
                                            </span>
                                        </td>

                                        <td>
                                            <button class="btn btn-sm btn-info btn-detail"
                                                data-pengiriman='@json($item)'>
                                                Detail
                                            </button>

                                            {{-- @if (auth()->user()->role == 'Kurir' && $item->status_pengiriman == 'Diproses') --}}
                                            <button class="btn btn-sm btn-success btn-ambil"
                                                data-id="{{ $item->pengiriman_id }}">
                                                Ambil Pengiriman
                                            </button>
                                            {{-- @endif --}}

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            Data pengiriman belum ada
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
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
        $(document).on('click', '.btn-detail', function() {

            let data = $(this).data('pengiriman');

            $('#d_pengiriman_id').text(data.pengiriman_id);
            $('#d_tanggal').text(data.tanggal_kirim);
            $('#d_status').text(data.status_pengiriman);
            $('#d_cabang').text(data.permintaan.cabang.nama_cabang ?? '-');

            let html = '';
            let no = 1;

            data.permintaan.detail.forEach(item => {
                html += `
            <tr>
                <td>${no++}</td>
                <td>${item.produk.nama_produk}</td>
                <td>${item.qty_permintaan}</td>
            </tr>
        `;
            });

            $('#detailProduk').html(html);

            $('#modalDetailPengiriman').modal('show');
        });

        $(document).on('click', '.btn-ambil', function() {
            let id = $(this).data('id');
            $('#ambil_pengiriman_id').val(id);
            $('#modalAmbilPengiriman').modal('show');
        });
    </script>
@endsection

<div class="modal fade" id="modalDetailPengiriman" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Pengiriman</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <table class="table table-sm">
                    <tr>
                        <th>ID Pengiriman</th>
                        <td id="d_pengiriman_id"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Kirim</th>
                        <td id="d_tanggal"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="d_status"></td>
                    </tr>
                    <tr>
                        <th>Cabang</th>
                        <td id="d_cabang"></td>
                    </tr>
                </table>

                <hr>

                <h6>Detail Produk</h6>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody id="detailProduk"></tbody>
                </table>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalAmbilPengiriman" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('pengiriman.ambil') }}">
                @csrf

                <input type="hidden" name="pengiriman_id" id="ambil_pengiriman_id">

                <div class="modal-header">
                    <h5 class="modal-title">Ambil Pengiriman</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Nama Kurir</label>
                        <input type="text" class="form-control" name="nama_kurir" value="{{ auth()->user()->name }}"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Barang diambil dalam kondisi baik"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button class="btn btn-success">Ambil Pengiriman</button>
                </div>

            </form>

        </div>
    </div>
</div>
