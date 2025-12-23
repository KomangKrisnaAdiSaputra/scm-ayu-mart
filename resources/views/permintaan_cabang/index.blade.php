@extends('layouts.app')
@section('titlePage', 'Permintaan Cabang')
@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">

            @if (auth()->user()->role == 'Cabang')
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('permintaancabang.form') }}"" class="btn btn-primary">Restok Produk</a>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Data Restok</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('permintaancabang') }}" class="mb-3">
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
                        @php
                            $isAllAccess = in_array(auth()->user()->role, ['Manager', 'Gudang']);
                        @endphp

                        <table class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>#</th>

                                    @if ($isAllAccess)
                                        <th>Cabang</th>
                                    @endif

                                    <th>Tanggal</th>
                                    <th>Jumlah Produk</th>
                                    <th>Total Qty</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($permintaan as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        @if ($isAllAccess)
                                            <td>
                                                {{ $item->cabang->nama_cabang ?? '-' }}
                                            </td>
                                        @endif

                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_permintaan)->format('d-m-Y') }}</td>

                                        {{-- jumlah jenis produk --}}
                                        <td>{{ $item->detail->count() }}</td>

                                        {{-- total qty --}}
                                        <td>{{ $item->detail->sum('qty_permintaan') }}</td>

                                        <td>
                                            <span
                                                class="badge badge-{{ $item->status_permintaan == 'Menunggu'
                                                    ? 'warning'
                                                    : ($item->status_permintaan == 'Diterima'
                                                        ? 'success'
                                                        : 'danger') }}">
                                                {{ $item->status_permintaan }}
                                            </span>
                                        </td>

                                        <td>
                                            <button class="btn btn-sm btn-info btn-detail"
                                                data-id="{{ $item->permintaan_id }}"
                                                data-cabang="{{ $item->cabang->nama_cabang ?? '-' }}"
                                                data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal_permintaan)->format('d-m-Y') }}"
                                                data-status="{{ $item->status_permintaan }}"
                                                data-detail='@json($item->detail)'>
                                                Detail
                                            </button>

                                            @if (auth()->user()->role == 'Gudang' && $item->status_permintaan == 'Menunggu')
                                                <form method="POST"
                                                    action="{{ route('permintaancabang.updatestatus', $item->permintaan_id) }}"
                                                    class="d-inline" onsubmit="return confirm('Terima permintaan ini?')">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Diterima">
                                                    <button class="btn btn-sm btn-success">Terima</button>
                                                </form>

                                                <form method="POST"
                                                    action="{{ route('permintaancabang.updatestatus', $item->permintaan_id) }}"
                                                    class="d-inline" onsubmit="return confirm('Tolak permintaan ini?')">
                                                    @csrf
                                                    <input type="hidden" name="status" value="Ditolak">
                                                    <button class="btn btn-sm btn-danger">Tolak</button>
                                                </form>
                                            @endif
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAllAccess ? 6 : 5 }}" class="text-center">
                                            Data permintaan belum ada
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
        document.querySelectorAll('.btn-detail').forEach(button => {
            button.addEventListener('click', function() {

                document.getElementById('modal-cabang').innerText = this.dataset.cabang;
                document.getElementById('modal-tanggal').innerText = this.dataset.tanggal;
                document.getElementById('modal-status').innerText = this.dataset.status;

                let detail = JSON.parse(this.dataset.detail);
                let tbody = document.getElementById('modal-detail-body');
                tbody.innerHTML = '';

                detail.forEach((item, index) => {
                    tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.produk.nama_produk}</td>
                        <td>${item.qty_permintaan}</td>
                    </tr>
                `;
                });

                $('#detailModal').modal('show');
            });
        });
    </script>
@endsection

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Permintaan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <p><strong>Cabang:</strong> <span id="modal-cabang"></span></p>
                <p><strong>Tanggal:</strong> <span id="modal-tanggal"></span></p>
                <p><strong>Status:</strong> <span id="modal-status"></span></p>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody id="modal-detail-body"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>
