@extends('layouts.app')
@section('titlePage', 'Purchase Order')

@section('css')
    <style>
        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
    </style>
@endsection

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
                                    <button class="btn btn-primary"
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
                                    <th>Kode</th>
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
                                        <td>{{ $item->kode_po }}</td>
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

                                            @php
                                                $role = auth()->user()->role;
                                                $status = $item->status_po;
                                                $pembayaran = $item->status_pembayaran;

                                                $bolehUbahStatus = false;

                                                if ($role === 'Gudang') {
                                                    if (
                                                        in_array($status, [
                                                            'Draft',
                                                            'Menunggu Persetujuan',
                                                            'Dikirim Supplier',
                                                        ])
                                                    ) {
                                                        $bolehUbahStatus = true;
                                                    }
                                                }

                                                if ($role === 'Manajer') {
                                                    if ($status === 'Menunggu Persetujuan') {
                                                        $bolehUbahStatus = true;
                                                    }
                                                }

                                                if ($role === 'Supplier') {
                                                    if (in_array($status, ['Disetujui Manajer', 'Diterima Supplier'])) {
                                                        $bolehUbahStatus = true;
                                                    }

                                                    // RULE KHUSUS: sudah diterima tapi belum bayar → LOCK
                                                    if (
                                                        $status === 'Diterima Supplier' &&
                                                        $pembayaran === 'Belum Bayar'
                                                    ) {
                                                        $bolehUbahStatus = false;
                                                    }
                                                }
                                            @endphp

                                            @if ($bolehUbahStatus)
                                                <button class="btn btn-sm btn-warning" data-toggle="modal"
                                                    data-target="#modalUpdateStatus"
                                                    onclick="openUpdateStatusModal({{ $item }}, '{{ auth()->user()->role }}')">
                                                    Ubah Status
                                                </button>
                                            @endif

                                            @if (!$item->invoice && auth()->user()->role == 'Supplier' && $item->status_po == 'Diterima Supplier')
                                                <button type="button" class="btn btn-sm btn-primary btn-buat-invoice"
                                                    data-po-id="{{ $item->po_id }}">
                                                    Buat Invoice
                                                </button>
                                            @elseif(in_array(auth()->user()->role, ['Manajer', 'Supplier']) && $item->invoice)
                                                <button type="button" class="btn btn-success btn-sm btn-bayar-invoice"
                                                    data-role='@json(auth()->user()->role)'
                                                    data-invoice='@json($item->invoice)'
                                                    data-po='@json($item)'
                                                    data-invoice-payment='@json($item->invoice->payment)'
                                                    data-payment-lists='@json($paymentLists)'>
                                                    Invoice
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
        const metodeSelect = document.getElementById('metode_bayar');
        const previewWrapper = document.getElementById('preview_metode_wrapper');
        const previewPhoto = document.getElementById('preview_metode_photo');
        const previewDescription = document.getElementById('preview_metode_description');

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

        window.routes = {
            invoicePayment: "{{ route('invoice.payment', ':id') }}",
            invoiceReject: "{{ route('invoice.reject', ':id') }}",
            invoiceCreate: "{{ route('invoice.create', ':id') }}",
            poUpdateStatus: "{{ route('purchaseorder.update.status', ':id') }}",
        };

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

            $('#po_id').val(po.po_id);

            // info utama
            $('#d_po_kode').text(po.kode_po);
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

        document.addEventListener('DOMContentLoaded', function() {

            const formBayar = document.getElementById('formBayarInvoice');
            const formTolak = document.getElementById('formTolakInvoice');

            const toggleTolak = document.getElementById('toggleTolakInvoice');
            const btnTolakInvoice = document.getElementById('btnTolakInvoice');
            const wrapperTolak = document.getElementById('wrapperTolakInvoice');
            const catatanSupplier = document.getElementById('catatan_supplier');

            const btnBayar = document.getElementById('btnSimpanPembayaran');
            const buktiWrapper = document.getElementById('bukti_preview_wrapper');

            const formInputs = formBayar.querySelectorAll('input,select,textarea');

            /* ================= TOGGLE ================= */
            toggleTolak.addEventListener('change', function() {
                catatanSupplier.readOnly = !this.checked;
                btnTolakInvoice.classList.toggle('d-none', !this.checked);
            });

            /* ================= OPEN MODAL ================= */
            document.querySelectorAll('.btn-bayar-invoice').forEach(btn => {
                btn.addEventListener('click', function() {

                    const invoice = JSON.parse(this.dataset.invoice);
                    const payment = this.dataset.invoicePayment ?
                        JSON.parse(this.dataset.invoicePayment) :
                        null;
                    const role = this.dataset.role.replace(/"/g, '');
                    const po = JSON.parse(this.dataset.po);

                    const paymentLists = JSON.parse(this.dataset.paymentLists);

                    const metodeSelect = document.getElementById('metode_bayar');

                    previewWrapper.classList.add('d-none');
                    previewPhoto.src = '';
                    previewDescription.innerText = '';


                    /* RESET OPTION */
                    metodeSelect.innerHTML = '<option value="">Pilih Metode</option>';

                    /* FILTER METODE */
                    paymentLists.forEach(item => {
                        if (String(item.created_by) === String(po.supplier.users_id)) {
                            appendMetode(item);
                        }
                    });

                    function appendMetode(item) {
                        const option = document.createElement('option');
                        option.value = item.name;
                        option.textContent = item.name;
                        option.dataset.photo = item.photo ? '/' + item.photo : null;
                        option.dataset.description = item.description ?? '';
                        metodeSelect.appendChild(option);
                    }

                    /* RESET */
                    formBayar.reset();
                    toggleTolak.checked = false;
                    toggleTolak.disabled = false;

                    wrapperTolak.classList.add('d-none');
                    btnBayar.classList.add('d-none');
                    buktiWrapper.classList.add('d-none');

                    catatanSupplier.readOnly = true;

                    formInputs.forEach(el => el.disabled = false);

                    /* DATA */
                    document.getElementById('invoice_id').value = invoice.invoice_id;
                    document.getElementById('nomor_invoice').innerText = invoice.nomor_invoice;
                    document.getElementById('tanggal_invoice').innerText = invoice.tanggal_invoice;
                    document.getElementById('total_invoice').innerText = formatRupiah(invoice
                        .total_invoice);

                    catatanSupplier.value = invoice.catatan_supplier ?? '';

                    document.getElementById('jumlah_bayar_view').value = formatRupiah(invoice
                        .total_invoice);
                    document.getElementById('jumlah_bayar').value = parseInt(invoice.total_invoice);

                    const statusEl = document.getElementById('status_invoice');
                    statusEl.className = 'badge';
                    statusEl.innerText = invoice.status_invoice;

                    /* MENUNGGU PEMBAYARAN */
                    if (invoice.status_invoice === 'Menunggu Pembayaran') {
                        statusEl.classList.add('badge-warning');

                        if (role === 'Manajer') {
                            btnBayar.classList.remove('d-none');
                            formBayar.action = window.routes.invoicePayment.replace(':id', invoice
                                .invoice_id);
                        } else {
                            formInputs.forEach(el => el.disabled = true);
                        }
                    }

                    /* PAYMENT EXIST */
                    if (payment) {

                        if (invoice.status_invoice === 'Lunas') {
                            statusEl.classList.add('badge-success');
                            formInputs.forEach(el => {
                                if (el.id !== 'catatan_supplier') el.disabled = true;
                            });

                        } else if (invoice.status_invoice === 'Ditolak') {
                            statusEl.classList.add('badge-danger');

                            if (role === 'Manajer') {
                                btnBayar.classList.remove('d-none');
                                formBayar.action = window.routes.invoicePayment.replace(':id',
                                    invoice.invoice_id);
                            }
                        }

                        formBayar.querySelector('[name="tanggal_bayar"]').value =
                            payment.tanggal_bayar.split('T')[0];

                        formBayar.querySelector('[name="metode_bayar"]').value =
                            payment.metode_bayar;

                        document.getElementById('jumlah_bayar_view').value = formatRupiah(payment
                            .jumlah_bayar);
                        document.getElementById('catatan_manajer').value = payment
                            .catatan_manajer ?? '';

                        if (payment.bukti_pembayaran) {
                            document.getElementById('bukti_preview').href = '/' + payment
                                .bukti_pembayaran;
                            buktiWrapper.classList.remove('d-none');
                        }

                        /* SUPPLIER */
                        if (role === 'Supplier') {
                            formInputs.forEach(el => {
                                if (el.id !== 'catatan_supplier') el.disabled = true;
                            });

                            if (
                                invoice.status_invoice === 'Lunas' &&
                                po.status_po !== 'Dikirim Supplier'
                            ) {
                                wrapperTolak.classList.remove('d-none');
                            }

                            toggleTolak.disabled = false;
                        }
                    }

                    $('#modalBayarInvoice').modal('show');
                });
            });

            /* ================= TOLAK INVOICE ================= */
            btnTolakInvoice.addEventListener('click', function() {

                if (!catatanSupplier.value.trim()) {
                    alert('Catatan supplier wajib diisi');
                    return;
                }

                if (!confirm('Yakin ingin menolak invoice ini?')) return;

                const invoiceId = document.getElementById('invoice_id').value;

                document.getElementById('invoice_id_tolak').value = invoiceId;
                document.getElementById('catatan_supplier_hidden').value = catatanSupplier.value;

                formTolak.action = window.routes.invoiceReject.replace(':id', invoiceId);
                formTolak.submit();
            });

        });

        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn-buat-invoice');
            const form = document.getElementById('formBuatInvoice');

            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const poId = this.dataset.poId;

                    // Set action form sesuai PO yang dipilih
                    form.action = window.routes.invoiceCreate.replace(':id', poId);

                    // Tampilkan modal
                    $('#modalBuatInvoice').modal('show');
                });
            });
        });

        function openUpdateStatusModal(po, role) {
            document.getElementById('po_id').value = po.po_id;
            document.getElementById('status_po').value = po.status_po;

            $('#formUpdateStatus').attr(
                'action',
                window.routes.poUpdateStatus.replace(':id', po.po_id)
            );

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
        }
    </script>

@endsection

<div class="modal fade" id="modalDetailPO" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title">Detail Purchase Order</h5>

                <button type="button" class="close ml-3" data-dismiss="modal">&times;</button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">

                {{-- INFO UTAMA --}}
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <th width="150">Kode PO</th>
                        <td id="d_po_kode"></td>
                    </tr>
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
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form id="formTolakInvoice" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" id="invoice_id_tolak">
                <input type="hidden" name="catatan_supplier" id="catatan_supplier_hidden">
            </form>

            <form id="formBayarInvoice" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-file-invoice"></i> Informasi Invoice
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="border rounded p-3 mb-3 bg-light">

                        <!-- TOGGLE TOLAK -->
                        <div id="wrapperTolakInvoice" class="form-check mb-2 d-none">
                            <input class="form-check-input" type="checkbox" id="toggleTolakInvoice">
                            <label class="form-check-label text-danger" for="toggleTolakInvoice">
                                Tolak Invoice
                            </label>
                        </div>

                        <input type="hidden" id="invoice_id">

                        <div><strong>No Invoice:</strong> <span id="nomor_invoice"></span></div>
                        <div><strong>Tanggal:</strong> <span id="tanggal_invoice"></span></div>
                        <div><strong>Status:</strong> <span id="status_invoice" class="badge"></span></div>
                        <div><strong>Total:</strong> Rp <span id="total_invoice"></span></div>

                        <label class="mt-2"><strong>Catatan Supplier</strong></label>
                        <textarea id="catatan_supplier" class="form-control" rows="4" readonly></textarea>
                    </div>

                    <h6 class="text-primary">
                        <i class="fa fa-money-bill"></i> Pembayaran
                    </h6>

                    <div class="form-group">
                        <label>Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Jumlah Bayar</label>
                        <input type="text" id="jumlah_bayar_view" class="form-control" readonly>
                        <input type="hidden" id="jumlah_bayar" name="jumlah_bayar">
                    </div>

                    <div class="form-group">
                        <label>Metode</label>
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

                    <div id="bukti_preview_wrapper" class="mb-2 d-none">
                        <a href="#" target="_blank" id="bukti_preview" class="btn btn-info btn-sm">
                            Lihat Bukti
                        </a>
                    </div>

                    <div class="form-group">
                        <label>Upload Bukti</label>
                        <input type="file" name="bukti_pembayaran" class="form-control-file">
                    </div>

                    <div class="form-group">
                        <label>Catatan Manajer</label>
                        <textarea id="catatan_manajer" class="form-control" rows="2" name="catatan_manajer"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                        Tutup
                    </button>

                    <button type="button" class="btn btn-danger d-none" id="btnTolakInvoice">
                        Simpan Tolak
                    </button>

                    <button type="submit" class="btn btn-success d-none" id="btnSimpanPembayaran">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="modalBuatInvoice" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form id="formBuatInvoice" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Buat Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Catatan Supplier <small class="text-muted">(optional)</small></label>
                        <textarea name="catatan_supplier" class="form-control" rows="3" placeholder="Tambahkan catatan jika ada..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Buat Invoice
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="modalUpdateStatus" tabindex="-1" role="dialog"
    aria-labelledby="modalUpdateStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalUpdateStatusLabel">
                    Ubah Status PO
                </h6>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="" method="POST" id="formUpdateStatus">
                @csrf

                <div class="modal-body">
                    <input type="hidden" name="po_id" id="po_id">

                    <div class="form-group mb-0">
                        <label class="mb-1 font-weight-bold small">
                            Status PO
                        </label>
                        <select name="status_po" id="status_po" class="form-control form-control-sm">
                            <option value="">-- Pilih Status --</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Update
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
