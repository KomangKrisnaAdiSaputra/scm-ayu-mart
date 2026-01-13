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
                                <th>Jenis Retur</th>
                                <th>Action</th>
                            </tr>

                            @forelse ($retur as $key => $item)
                                @php
                                    $produk = $allProduk->where('id_produk', $item->produk_id)->first();
                                @endphp
                                <tr>
                                    <td>{{ $retur->firstItem() + $key }}</td>
                                    <td>{{ $item->purchaseOrder->kode_po ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $produk->nama_produk }}</strong><br>
                                        <small class="text-muted">
                                            {{ $produk->kode_produk }}
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

                                    <td>{{ $item->payment ? 'Pengembalian Dana' : 'Pengembalian Barang' }}</td>

                                    <td class="text-nowrap">

                                        {{-- DETAIL --}}
                                        <button type="button" class="btn btn-info btn-sm mb-1"
                                            onclick='openDetailRetur(@json($item))' data-toggle="modal"
                                            data-target="#modalDetailRetur">
                                            Detail
                                        </button>

                                        {{-- ================= SUPPLIER ================= --}}
                                        @if (auth()->user()->role === 'Supplier')
                                            {{-- MENUNGGU KONFIRMASI --}}
                                            @if ($item->status_retur === 'Menunggu Konfirmasi')
                                                <button type="button" class="btn btn-success btn-sm mb-1"
                                                    onclick="openTerimaRetur({{ $item->retur_id }})" data-toggle="modal"
                                                    data-target="#modalTerimaRetur">
                                                    Terima
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm mb-1 btn-tolak-retur"
                                                    data-id="{{ $item->retur_id }}">
                                                    Tolak
                                                </button>
                                            @endif

                                            {{-- DITERIMA & BELUM DIBAYAR --}}
                                            @if ($item->status_retur === 'Diterima' && $item->payment === 0)
                                                <form action="{{ route('retur.kirim', $item->retur_id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-warning btn-sm mb-1">
                                                        Kirim Barang
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- BAYAR REFUND --}}
                                            @if ($item?->tb_payment?->status === 'Menunggu Pembayaran')
                                                <button type="button" class="btn btn-success btn-sm mb-1 btn-bayar-refund"
                                                    data-retur-id="{{ $item->retur_id }}" data-po-id="{{ $item->po_id }}"
                                                    data-jumlah="{{ $item->tb_payment->jumlah }}" data-toggle="modal"
                                                    data-target="#modalBayarRefund"
                                                    data-payment-lists='@json($paymentLists)'>
                                                    Bayar Refund
                                                </button>
                                            @endif
                                        @endif

                                        {{-- ================= GUDANG ================= --}}
                                        @if (auth()->user()->role === 'Gudang' && $item->status_retur === 'Dikirim')
                                            <form action="{{ route('retur.selesai', $item->retur_id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-primary btn-sm mb-1">
                                                    Selesai
                                                </button>
                                            </form>
                                        @endif

                                        {{-- ================= MANAJER ================= --}}
                                        @if (auth()->user()->role === 'Manajer')
                                            {{-- BUAT PAYMENT --}}
                                            @if ($item->payment === 1 && !($item?->tb_payment ?? null))
                                                <form action="{{ route('retur.store.payment') }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="retur_id" value="{{ $item->retur_id }}">
                                                    <input type="hidden" name="jumlah"
                                                        value="{{ $produk->harga_beli * $item->qty_retur }}">

                                                    <button class="btn btn-primary btn-sm mb-1">
                                                        Buat Payment
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- SUDAH DIBAYAR --}}
                                            @if ($item->status_retur === 'Dibayar')
                                                <button type="button" class="btn btn-info btn-sm mb-1"
                                                    onclick='openDetailPayment(@json($item->tb_payment))'
                                                    data-toggle="modal" data-target="#modalDetailReturPayment">
                                                    Lihat Payment
                                                </button>

                                                <form action="{{ route('retur.selesai', $item->retur_id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm mb-1">
                                                        Selesaikan
                                                    </button>
                                                </form>
                                            @endif
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
        const metodeSelect = document.getElementById('metode_pembayaran_refund');
        const previewWrapper = document.getElementById('preview_metode_wrapper_refund');
        const previewPhoto = document.getElementById('preview_metode_photo_refund');
        const previewDescription = document.getElementById('preview_metode_description_refund');

        window.routes = {
            tolakRetur: "{{ route('retur.tolak', ':id') }}",
        };

        metodeSelect.addEventListener('change', function() {

            const selected = this.selectedOptions[0];

            // RESET
            previewWrapper.classList.add('d-none');
            previewPhoto.src = '';
            previewDescription.innerText = '';

            if (!selected || !selected.dataset.photo) return;
            if (selected.dataset.photo != "null") {
                previewPhoto.src = selected.dataset.photo;
                previewPhoto.classList.remove('d-none');
            } else {
                previewPhoto.classList.add('d-none');
            }
            previewDescription.innerText = selected.dataset.description || '';

            previewWrapper.classList.remove('d-none');
        });

        function openDetailRetur(retur) {
            const allProduk = @json($allProduk);
            const produk = allProduk.find(a => a.id_produk == retur.produk_id);

            // RETUR
            $('#r_tanggal').text(retur.tanggal_retur);
            $('#r_qty').text(retur.qty_retur);
            $('#r_status').text(retur.status_retur);
            $('#r_alasan').text(retur.alasan);
            $('#r_catatan').text(retur.catatan);

            // PRODUK
            $('#p_kode').text(produk.kode_produk);
            $('#p_nama').text(produk.nama_produk);
            $('#p_kategori').text(produk.jenis.nama_jenis);
            $('#p_satuan').text(produk.satuan);
            $('#p_harga').text(formatRupiah(produk.harga_beli));

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

        document.querySelectorAll('.btn-bayar-refund').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('retur_id').value = this.dataset.returId;
                document.getElementById('po_id').value = this.dataset.poId;
                const jumlah = this.dataset.jumlah;

                document.getElementById('jumlah').value = jumlah; // TANPA FORMAT
                document.getElementById('jumlah_display').value = formatRupiah(jumlah);


                const paymentLists = JSON.parse(this.dataset.paymentLists);

                const metodeSelect = document.getElementById('metode_pembayaran_refund');

                previewWrapper.classList.add('d-none');
                previewPhoto.src = '';
                previewDescription.innerText = '';


                /* RESET OPTION */
                metodeSelect.innerHTML = '<option value="">Pilih Metode</option>';
                console.log(paymentLists);

                paymentLists.forEach(item => {
                    appendMetode(item);
                });

                function appendMetode(item) {
                    const option = document.createElement('option');
                    option.value = item.name;
                    option.textContent = item.name;
                    option.dataset.photo = item.photo ? '/' + item.photo : null;
                    option.dataset.description = item.description ?? '';
                    metodeSelect.appendChild(option);
                }
            });
        });

        function openDetailPayment(payment) {
            $('#dp_metode').text(payment.metode_pembayaran);
            $('#dp_jumlah').text(formatRupiah(payment.jumlah));
            $('#dp_tanggal').text(payment.tanggal_pembayaran);
            $('#dp_status').text(payment.status);
            $('#dp_keterangan').text(payment.keterangan ?? '-');

            if (payment.bukti_pembayaran) {
                $('#dp_bukti')
                    .attr('href', `/${payment.bukti_pembayaran}`)
                    .show();
            } else {
                $('#dp_bukti').hide();
            }
        }

        $(document).on('click', '.btn-tolak-retur', function() {
            const id = $(this).data('id');

            $('#tolakReturForm').attr(
                'action',
                window.routes.tolakRetur.replace(':id', id)
            );

            $('#tolakReturModal').modal('show');
        });
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
                    <tr>
                        <th>Catatan</th>
                        <td id="r_catatan"></td>
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
                        <select name="metode_bayar" id="metode_bayar" class="form-control">
                            <option value="">Pilih</option>
                            <option>Transfer Bank</option>
                            <option>Cash</option>
                            <option>QRIS</option>
                        </select>

                        <div id="preview_metode_wrapper" class="mt-3 d-none">
                            <div class="text-center">
                                <img id="preview_metode_photo" src="" alt="Metode"
                                    style="max-width:200px;max-height:200px;object-fit:contain;" class="mb-2 rounded">
                            </div>

                            <div class="text-muted text-center" id="preview_metode_description"></div>
                        </div>
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

<div class="modal fade" id="modalBayarRefund" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('retur.pay.payment') }}" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="retur_id" id="retur_id">
            <input type="hidden" name="po_id" id="po_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pembayaran Refund</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran_refund" class="form-control">
                            <option value="">Pilih</option>
                            <option>Transfer Bank</option>
                            <option>Cash</option>
                            <option>QRIS</option>
                        </select>

                        <div id="preview_metode_wrapper_refund" class="mt-3 d-none">
                            <div class="text-center">
                                <img id="preview_metode_photo_refund" src="" alt="Metode"
                                    style="max-width:200px;max-height:200px;object-fit:contain;" class="mb-2 rounded">
                            </div>

                            <div class="text-muted text-center" id="preview_metode_description_refund"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Jumlah Refund</label>

                        {{-- TAMPILAN (RUPIAH) --}}
                        <input type="text" id="jumlah_display" class="form-control" readonly>

                        {{-- VALUE ASLI --}}
                        <input type="hidden" name="jumlah" id="jumlah">
                    </div>


                    <div class="mb-3">
                        <label>Tanggal Pembayaran</label>
                        <input type="datetime-local" name="tanggal_pembayaran" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Bukti Pembayaran</label>
                        <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Bayar Refund</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetailReturPayment" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title">Detail Retur Payment</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Metode Pembayaran</th>
                        <td id="dp_metode"></td>
                    </tr>
                    <tr>
                        <th>Jumlah</th>
                        <td id="dp_jumlah"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Bayar</th>
                        <td id="dp_tanggal"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-success" id="dp_status"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td id="dp_keterangan"></td>
                    </tr>
                    <tr>
                        <th>Bukti</th>
                        <td>
                            <a href="" id="dp_bukti" target="_blank">Lihat Bukti</a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="tolakReturModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" id="tolakReturForm">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Retur</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Catatan Penolakan</label>
                        <textarea name="catatan" class="form-control" rows="4" placeholder="Tulis alasan penolakan..." required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button class="btn btn-danger" type="submit">Tolak Retur</button>
                </div>
            </div>
        </form>
    </div>
</div>
