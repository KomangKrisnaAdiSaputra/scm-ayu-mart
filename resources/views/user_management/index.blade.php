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
                                <input type="text" name="search" class="form-control" placeholder="Search user..."
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
