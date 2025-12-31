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
                                        {{-- DETAIL --}}
                                        <button class="btn btn-info btn-sm"
                                            onclick='openDetailRetur(@json($retur))' data-toggle="modal"
                                            data-target="#modalDetailRetur">
                                            Detail
                                        </button>

                                        {{-- SUPPLIER: TERIMA --}}
                                        @if (auth()->user()->role === 'Supplier' && $item->status_retur === 'Menunggu Konfirmasi')
                                            <button class="btn btn-success btn-sm"
                                                onclick="openTerimaRetur({{ $item->retur_id }})" data-toggle="modal"
                                                data-target="#modalTerimaRetur">
                                                Terima
                                            </button>

                                            <form action="{{ url('/retur/' . $item->retur_id . '/tolak') }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-danger btn-sm">Tolak</button>
                                            </form>
                                        @endif

                                        {{-- SUPPLIER: KIRIM BARANG --}}
                                        @if (auth()->user()->role === 'Supplier' && $item->payment === 0)
                                            <form action="{{ url('/retur/' . $item->id . '/kirim') }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-warning btn-sm">Kirim Barang</button>
                                            </form>
                                        @endif

                                        {{-- GUDANG: SELESAI --}}
                                        @if (auth()->user()->role === 'Gudang' && $item->status_retur === 'Dikirim Supplier')
                                            <form action="{{ url('/retur/' . $item->id . '/selesai') }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-primary btn-sm">Selesai</button>
                                            </form>
                                        @endif

                                        @if (auth()->user()->role === 'Manajer' && $item->payment === 1)
                                            <button class="btn btn-primary btn-sm"
                                                onclick="
    $('#rp_retur_id').val({{ $item->retur_id }});
    $('#rp_jumlah').val({{ $item->produk->harga_beli }});
"
                                                data-toggle="modal" data-target="#modalReturPayment">
                                                Buat Payment
                                            </button>
                                        @endif

                                        @if (auth()->user()->role === 'Supplier' && $item?->tb_payment?->status === 'Menunggu Pembayaran')
                                            <form action="{{ url('/retur-payment/' . $item->payment->id . '/bayar') }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-success btn-sm">Bayar Refund</button>
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

@section('js')
    <script>
        function openDetailRetur(retur) {
            console.log(retur);

            // RETUR
            $('#r_tanggal').text(retur.tanggal_retur);
            $('#r_qty').text(retur.qty_retur);
            $('#r_status').text(retur.status_retur);
            $('#r_alasan').text(retur.alasan);

            // PRODUK
            $('#p_kode').text(retur.produk.kode_produk);
            $('#p_nama').text(retur.produk.nama_produk);
            $('#p_kategori').text(retur.produk.kategori);
            $('#p_satuan').text(retur.produk.satuan);
            $('#p_harga').text(formatRupiah(retur.produk.harga_beli));

            // PO
            $('#po_kode').text(retur.purchase_order.kode_po);
            $('#po_status').text(retur.purchase_order.status_po);
            $('#po_bayar').text(retur.purchase_order.status_pembayaran);
            $('#po_total').text(formatRupiah(retur.purchase_order.total_po));
        }

        function formatRupiah(angka) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
        }

        function openTerimaRetur(returId) {
            $('#formTerimaRetur').attr('action', `/retur/${returId}/terima`);
        }
    </script>

@endsection

<div class="modal fade" id="modalDetailRetur" tabindex="-1" role="dialog" aria-labelledby="modalDetailReturLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalDetailReturLabel">
                    Detail Retur
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                {{-- INFORMASI RETUR --}}
                <h6 class="text-primary">Informasi Retur</h6>
                <table class="table table-sm table-bordered mb-3">
                    <tr>
                        <th width="35%">Tanggal Retur</th>
                        <td id="r_tanggal"></td>
                    </tr>
                    <tr>
                        <th>Qty Retur</th>
                        <td id="r_qty"></td>
                    </tr>
                    <tr>
                        <th>Status Retur</th>
                        <td>
                            <span class="badge badge-success" id="r_status"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td id="r_alasan"></td>
                    </tr>
                </table>

                {{-- PRODUK --}}
                <h6 class="text-primary">Detail Produk</h6>
                <table class="table table-sm table-bordered mb-3">
                    <tr>
                        <th width="35%">Kode Produk</th>
                        <td id="p_kode"></td>
                    </tr>
                    <tr>
                        <th>Nama Produk</th>
                        <td id="p_nama"></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td id="p_kategori"></td>
                    </tr>
                    <tr>
                        <th>Satuan</th>
                        <td id="p_satuan"></td>
                    </tr>
                    <tr>
                        <th>Harga Beli</th>
                        <td id="p_harga"></td>
                    </tr>
                </table>

                {{-- PURCHASE ORDER --}}
                <h6 class="text-primary">Purchase Order</h6>
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th width="35%">Kode PO</th>
                        <td id="po_kode"></td>
                    </tr>
                    <tr>
                        <th>Status PO</th>
                        <td>
                            <span class="badge badge-warning" id="po_status"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td>
                            <span class="badge badge-info" id="po_bayar"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Total PO</th>
                        <td id="po_total"></td>
                    </tr>
                </table>

            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="modalTerimaRetur" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title">Terima Retur</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form method="POST" id="formTerimaRetur">
                @csrf

                <div class="modal-body">
                    <label class="font-weight-bold small">Jenis Pengembalian</label>
                    <select name="jenis_retur" class="form-control form-control-sm" required>
                        <option value="">-- Pilih --</option>
                        <option value="barang">Pengembalian Barang</option>
                        <option value="dana">Pengembalian Dana</option>
                    </select>


                    <label class="mt-4">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder=""></textarea>
                </div>

                <div class="modal-footer py-2">
                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button class="btn btn-success btn-sm">Proses</button>
                </div>
            </form>

        </div>
    </div>
</div>


<div class="modal fade" id="modalReturPayment" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title">Retur Payment</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form action="{{ route('retur.store.payment') }}" method="POST">
                @csrf
                <input type="hidden" name="retur_id" id="rp_retur_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Jumlah Refund</label>
                        <input type="number" name="jumlah" id="rp_jumlah" class="form-control form-control-sm"
                            required readonly>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-control form-control-sm" required>
                            <option value="Transfer">Transfer</option>
                            {{-- <option value="Cash">Cash</option> --}}
                        </select>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary btn-sm">Buat Payment</button>
                </div>
            </form>

        </div>
    </div>
</div>
