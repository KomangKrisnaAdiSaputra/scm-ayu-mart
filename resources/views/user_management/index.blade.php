@extends('layouts.app')
@section('titlePage', 'Manajemen User')

@php
    $roleManajer = auth()->user()->role == 'Manajer';
@endphp
@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('usermanagement.form') }}"" class="btn btn-primary">Tambah Data</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data User</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('usermanagement') }}">
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
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                            @foreach ($user as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->username }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>{{ $item->role }}</td>
                                    <td>
                                        <div class="badge  {{ $item->is_active == 1 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $item->is_active == 1 ? 'Aktif' : 'Nonaktif' }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @php
                                            $cabang = $cabangs->where('users_id', $item->users_id)->first();
                                        @endphp
                                        <button class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#modalDetailUser" data-username="{{ $item->username }}"
                                            data-nama="{{ $item->nama }}" data-email="{{ $item->email }}"
                                            data-role="{{ $item->role }}" data-status="{{ $item->is_active }}"
                                            data-created="{{ $item->created_at }}" {{-- Cabang --}}
                                            data-nama-cabang="{{ $cabang->nama_cabang ?? '' }}"
                                            data-alamat-cabang="{{ $cabang->alamat ?? '' }}"
                                            data-kontak-cabang="{{ $cabang->kontak ?? '' }}" {{-- Supplier --}}
                                            data-nama-supplier="{{ $item->supplier->nama_supplier ?? '' }}"
                                            data-alamat-supplier="{{ $item->supplier->alamat ?? '' }}"
                                            data-kontak-supplier="{{ $item->supplier->kontak ?? '' }}"
                                            data-status-supplier="{{ $item->supplier->status_supplier ?? '' }}">
                                            Detail
                                        </button>


                                        @if ($roleManajer)
                                            <a href="{{ route('usermanagement.form', ['id' => $item->users_id]) }}"
                                                class="btn btn-secondary btn-sm mr-1">
                                                Edit
                                            </a>
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
        $('#modalDetailUser').on('show.bs.modal', function(event) {
            let b = $(event.relatedTarget)

            let role = b.data('role')

            $('#u_username').text(b.data('username'))
            $('#u_nama').text(b.data('nama'))
            $('#u_email').text(b.data('email'))
            $('#u_role').text(role)
            $('#u_created').text(b.data('created'))

            // Status User
            let userStatus = $('#u_status')
            userStatus
                .text(b.data('status') == 1 ? 'Aktif' : 'Nonaktif')
                .removeClass('badge-success badge-danger')
                .addClass(b.data('status') == 1 ? 'badge-success' : 'badge-danger')

            // Reset semua section
            $('.cabang, .supplier, #section-cabang, #section-supplier').hide()

            // CABANG
            if (role === 'Cabang') {
                $('#section-cabang, .cabang').show()
                $('#c_nama').text(b.data('nama-cabang'))
                $('#c_alamat').text(b.data('alamat-cabang'))
                $('#c_kontak').text(b.data('kontak-cabang'))
            }

            // SUPPLIER
            if (role === 'Supplier') {
                $('#section-supplier, .supplier').show()
                $('#s_nama').text(b.data('nama-supplier'))
                $('#s_alamat').text(b.data('alamat-supplier'))
                $('#s_kontak').text(b.data('kontak-supplier'))

                let badgeSupplier = $('#s_status')
                badgeSupplier
                    .text(b.data('status-supplier'))
                    .removeClass('badge-success badge-danger')
                    .addClass(
                        b.data('status-supplier') === 'aktif' ?
                        'badge-success' :
                        'badge-danger'
                    )
            }
        })
    </script>
@endsection

{{-- Modal Detail User --}}
<div class="modal fade" id="modalDetailUser" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <table class="table table-sm table-borderless">

                    <tr>
                        <th width="40%">Username</th>
                        <td id="u_username"></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td id="u_nama"></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td id="u_email"></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td id="u_role"></td>
                    </tr>
                    <tr>
                        <th>Status User</th>
                        <td><span id="u_status" class="badge"></span></td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td id="u_created"></td>
                    </tr>

                    {{-- CABANG --}}
                    <tr id="section-cabang" class="table-secondary">
                        <th colspan="2">Data Cabang</th>
                    </tr>
                    <tr class="cabang">
                        <th>Nama Cabang</th>
                        <td id="c_nama"></td>
                    </tr>
                    <tr class="cabang">
                        <th>Alamat</th>
                        <td id="c_alamat"></td>
                    </tr>
                    <tr class="cabang">
                        <th>Kontak</th>
                        <td id="c_kontak"></td>
                    </tr>

                    {{-- SUPPLIER --}}
                    <tr id="section-supplier" class="table-secondary">
                        <th colspan="2">Data Supplier</th>
                    </tr>
                    <tr class="supplier">
                        <th>Nama Supplier</th>
                        <td id="s_nama"></td>
                    </tr>
                    <tr class="supplier">
                        <th>Alamat</th>
                        <td id="s_alamat"></td>
                    </tr>
                    <tr class="supplier">
                        <th>Kontak</th>
                        <td id="s_kontak"></td>
                    </tr>
                    <tr class="supplier">
                        <th>Status Supplier</th>
                        <td><span id="s_status" class="badge"></span></td>
                    </tr>

                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>
