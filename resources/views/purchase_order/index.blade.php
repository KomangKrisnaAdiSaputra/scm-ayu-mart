@extends('layouts.app')
@section('titlePage', 'Purchase Order')
@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">

            @if (in_array(auth()->user()->role, ['Gudang', 'Manajer']))
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('purchaseorder.create') }}"" class="btn btn-primary">Form Purchase</a>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Data Purchase Order</h4>

                    <div class="card-header-action">
                        <form method="GET" action="{{ route('purchaseorder') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cari ID / Status / Tanggal" value="{{ $search ?? '' }}">
                                <div class="input-group-append">
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
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID PO</th>
                                    <th>Supplier</th>
                                    <th>Tanggal PO</th>
                                    <th>Total Produk</th>
                                    <th>Total Qty</th>
                                    <th>Total PO</th>
                                    <th>Status PO</th>
                                    <th>Pembayaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($po as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>PO-{{ $item->po_id }}</td>
                                        <td>{{ $item->supplier->nama_supplier ?? '-' }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($item->tanggal_po)->format('d-m-Y') }}
                                        </td>

                                        <td>{{ $item->detail->count() }}</td>
                                        <td>{{ $item->detail->sum('qty') }}</td>

                                        <td>
                                            <strong>Rp {{ number_format($item->total_po, 0, ',', '.') }}</strong>
                                        </td>

                                        {{-- STATUS PO --}}
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'Draft' => 'secondary',
                                                    'Menunggu Persetujuan' => 'warning',
                                                    'Disetujui Manajer' => 'success',
                                                    'Ditolak Manajer' => 'danger',
                                                    'Diterima Supplier' => 'success',
                                                    'Ditolak Supplier' => 'danger',
                                                    'Dikirim Supplier' => 'info',
                                                    'Selesai' => 'primary',
                                                ];
                                            @endphp

                                            <span
                                                class="badge badge-{{ $statusClasses[$item->status_po] ?? 'secondary' }}">
                                                {{ $item->status_po }}
                                            </span>
                                        </td>

                                        {{-- STATUS PEMBAYARAN --}}
                                        <td>
                                            <span
                                                class="badge badge-{{ $item->status_pembayaran == 'Sudah Bayar' ? 'success' : 'danger' }}">
                                                {{ $item->status_pembayaran }}
                                            </span>
                                        </td>

                                        {{-- AKSI --}}
                                        <td class="text-nowrap">
                                            <button class="btn btn-info btn-sm btn-detail"
                                                data-po='@json($item)'>
                                                Detail
                                            </button>

                                            @if (!$item->invoice && auth()->user()->role == 'Supplier' && $item->status_po == 'Diterima Supplier')
                                                <form action="{{ url('/invoice/create/' . $item->po_id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-primary">Buat Invoice</button>
                                                </form>
                                            @elseif(auth()->user()->role == 'Manajer' && $item->invoice)
                                                <button class="btn btn-success btn-sm btn-bayar-invoice"
                                                    data-invoice='@json($item->invoice)'
                                                    data-po='@json($item)' data-toggle="modal"
                                                    data-target="#modalBayarInvoice">
                                                    Bayar
                                                </button>
                                            @endif

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            Data Purchase Order belum ada
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
        const rules = {
            Gudang: {
                'Draft': ['Draft', 'Menunggu Persetujuan'],
                'Dikirim Supplier': ['Selesai'],
                'Menunggu Persetujuan': ['Draft', 'Menunggu Persetujuan'],
            },
            Manajer: {
                'Menunggu Persetujuan': ['Disetujui Manajer', 'Ditolak Manajer'],
            },
            Supplier: {
                'Disetujui Manajer': ['Diterima Supplier', 'Ditolak Supplier'],
                'Diterima Supplier': ['Dikirim Supplier'],
            }
        };

        $('.btn-detail').on('click', function() {
            const po = $(this).data('po');
            const role = "{{ auth()->user()->role }}";

            // set form action
            $('#formUpdateStatus').attr(
                'action',
                `/purchase-order/${po.po_id}/update/status`
            );

            $('#po_id').val(po.po_id);

            // info utama
            $('#d_po_id').text('PO-' + po.po_id);
            $('#d_supplier').text(po.supplier?.nama_supplier ?? '-');
            $('#d_tanggal').text(po.tanggal_po);
            $('#d_status_po').text(po.status_po);
            $('#d_status_bayar').text(po.status_pembayaran);

            // detail produk
            let html = '';
            let total = 0;

            po.detail.forEach((item, i) => {
                const subtotal = item.qty * item.harga;
                total += subtotal;

                html += `
            <tr>
                <td>${i + 1}</td>
                <td>${item.produk.nama_produk}</td>
                <td>${item.qty}</td>
                <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
                <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
            </tr>
        `;
            });

            $('#detailProdukPO').html(html);
            $('#d_total_po').text('Rp ' + total.toLocaleString('id-ID'));

            // status dropdown
            const allowed = rules[role]?.[po.status_po] ?? [];
            // let options = `<option value="" readonly>Select</option>`;
            let options = '';

            allowed.forEach(status => {
                options += `<option value="${status}">${status}</option>`;
            });

            if (options) {
                $('#status_po').html(options);
                $('#formUpdateStatus').show();
            } else {
                $('#formUpdateStatus').hide();
            }

            if (po.status_po == 'Diterima Supplier' && po.status_pembayaran == 'Belum Bayar') {
                $('#formUpdateStatus').hide();
            }
            $('#modalDetailPO').modal('show');
        });

        // ===== HELPER =====
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function formatTanggal(tgl) {
            return new Date(tgl).toLocaleDateString('id-ID');
        }

        function badgeStatusPO(status) {
            let map = {
                'Draft': 'secondary',
                'Menunggu Persetujuan': 'warning',
                'Disetujui Manajer': 'success',
                'Ditolak Manajer': 'danger',
                'Selesai': 'success'
            };
            return `<span class="badge badge-${map[status] ?? 'secondary'}">${status}</span>`;
        }

        function badgeStatusBayar(status) {
            return status === 'Sudah Bayar' ?
                `<span class="badge badge-success">${status}</span>` :
                `<span class="badge badge-danger">${status}</span>`;
        }

        document.querySelectorAll('.btn-bayar-invoice').forEach(btn => {
            btn.addEventListener('click', function() {

                const invoice = JSON.parse(this.dataset.invoice);

                document.getElementById('invoice_id').value = invoice.invoice_id;
                document.getElementById('nomor_invoice').innerText = invoice.nomor_invoice;
                document.getElementById('total_invoice').innerText =
                    new Intl.NumberFormat('id-ID').format(invoice.total_invoice);

                const total = invoice.total_invoice;

                // tampilkan format rupiah
                document.getElementById('jumlah_bayar_view').value = new Intl.NumberFormat('id-ID').format(
                    total);

                // value numeric ke backend
                document.getElementById('jumlah_bayar').value = total;

                // Action form
                document.getElementById('formBayarInvoice').action =
                    `/invoice/payment/${invoice.invoice_id}`;

                // Jika invoice ditolak
                if (invoice.status_invoice === 'Ditolak') {
                    document.getElementById('alasan_ditolak').innerText = invoice.alasan_ditolak;
                    document.getElementById('alasan_ditolak_wrapper').classList.remove('d-none');
                } else {
                    document.getElementById('alasan_ditolak_wrapper').classList.add('d-none');
                }

            });
        });
    </script>

@endsection

<div class="modal fade" id="modalDetailPO" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title">Detail Purchase Order</h5>

                {{-- FORM UPDATE STATUS --}}
                <form action="" method="POST" id="formUpdateStatus" class="form-inline">
                    @csrf
                    <input type="hidden" name="po_id" id="po_id">

                    <select name="status_po" id="status_po" class="form-control form-control-sm mr-2">
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm">
                        Update
                    </button>
                </form>

                <button type="button" class="close ml-3" data-dismiss="modal">&times;</button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">

                {{-- INFO UTAMA --}}
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <th width="150">ID PO</th>
                        <td id="d_po_id"></td>
                    </tr>
                    <tr>
                        <th>Supplier</th>
                        <td id="d_supplier"></td>
                    </tr>
                    <tr>
                        <th>Tanggal PO</th>
                        <td id="d_tanggal"></td>
                    </tr>
                    <tr>
                        <th>Status PO</th>
                        <td id="d_status_po"></td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td id="d_status_bayar"></td>
                    </tr>
                </table>

                <hr>

                {{-- DETAIL PRODUK --}}
                <h6 class="mb-2">Detail Produk</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detailProdukPO"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Total</th>
                                <th id="d_total_po"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalBayarInvoice" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form id="formBayarInvoice" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Pembayaran Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="invoice_id" id="invoice_id">

                    <div class="mb-2">
                        <strong>No Invoice:</strong>
                        <span id="nomor_invoice">-</span>
                    </div>

                    <div class="mb-2">
                        <strong>Total:</strong>
                        Rp <span id="total_invoice">0</span>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Bayar</label>

                        <!-- Tampilan (formatted) -->
                        <input type="text" id="jumlah_bayar_view" class="form-control" readonly>

                        <!-- Data ke backend -->
                        <input type="hidden" name="jumlah_bayar" id="jumlah_bayar">
                    </div>


                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="metode_bayar" class="form-control" required>
                            <option value="">Pilih</option>
                            <option>Transfer Bank</option>
                            <option>Cash</option>
                            <option>QRIS</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Bukti Pembayaran</label>
                        <input type="file" name="bukti_pembayaran" class="form-control-file">
                    </div>

                    <div id="alasan_ditolak_wrapper" class="d-none">
                        <div class="alert alert-danger">
                            <strong>Ditolak:</strong>
                            <span id="alasan_ditolak"></span>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        Simpan Pembayaran
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
