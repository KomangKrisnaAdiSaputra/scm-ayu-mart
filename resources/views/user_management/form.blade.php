@extends('layouts.app')
@section('titlePage', 'Manajemen User')

@php
    $breadcrumbs = [
        ['label' => 'Manajemen User', 'url' => route('usermanagement'), 'active' => 'active'],
        ['label' => isset($user) ? 'Edit User' : 'Tambah User', 'url' => '', 'active' => ''],
    ];
@endphp

@section('app')
    <h2 class="section-title">Form User</h2>
    <p class="section-lead">
        {{ isset($user) ? 'Edit' : 'Tambah' }} Data User
    </p>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">

                <form method="POST" action="{{ route('usermanagement.save', $user->users_id ?? null) }}">
                    @csrf

                    {{-- HEADER --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>User</h4>

                        {{-- STATUS RADIO --}}
                        <div class="d-flex align-items-center">
                            <label class="mr-3 mb-0 font-weight-bold">Status:</label>

                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_aktif" name="is_active" value="1"
                                    class="custom-control-input"
                                    {{ old('is_active', isset($user) ? $user->is_active : 1) == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label text-success" for="status_aktif">
                                    Aktif
                                </label>
                            </div>

                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_nonaktif" name="is_active" value="0"
                                    class="custom-control-input"
                                    {{ old('is_active', isset($user) ? $user->is_active : 1) == 0 ? 'checked' : '' }}>
                                <label class="custom-control-label text-danger" for="status_nonaktif">
                                    Nonaktif
                                </label>
                            </div>
                        </div>

                    </div>

                    {{-- BODY --}}
                    <div class="card-body">
                        <div class="row">

                            {{-- KIRI --}}
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username"
                                        class="form-control @error('username') is-invalid @enderror"
                                        value="{{ old('username', $user->username ?? '') }}">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="nama"
                                        class="form-control @error('nama') is-invalid @enderror"
                                        value="{{ old('nama', $user->nama ?? '') }}">
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email ?? '') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            {{-- KANAN --}}
                            <div class="col-md-6">

                                {{-- PASSWORD (HANYA CREATE) --}}
                                @if (!isset($user))
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control @error('role') is-invalid @enderror">
                                        <option value="">-- Pilih Role --</option>
                                        @foreach (['Manajer', 'Gudang', 'Cabang', 'Supplier', 'Kurir'] as $role)
                                            <option value="{{ $role }}"
                                                {{ old('role', $user->role ?? '') == $role ? 'selected' : '' }}>
                                                {{ $role }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NAMA CABANG (KHUSUS CABANG) --}}
                                <div class="form-group d-none" id="field-nama-cabang">
                                    <label>Nama Cabang</label>
                                    <input type="text" name="nama_cabang" class="form-control"
                                        value="{{ old('nama_cabang', $detail->nama_cabang ?? '') }}">
                                </div>

                                {{-- ALAMAT & KONTAK (CABANG & SUPPLIER) --}}
                                <div id="field-alamat-kontak" class="d-none">

                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea name="alamat" class="form-control">{{ old('alamat', $detail->alamat ?? '') }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Kontak</label>
                                        <input type="text" name="kontak" class="form-control"
                                            value="{{ old('kontak', $detail->kontak ?? '') }}">
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="card-footer text-right">
                        <a href="{{ route('usermanagement') }}" class="btn btn-secondary mr-2">Batal</a>
                        <button class="btn btn-primary">
                            {{ isset($user) ? 'Update' : 'Simpan' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function toggleRoleFields(role) {
            const alamatKontak = document.getElementById('field-alamat-kontak');
            const namaCabang = document.getElementById('field-nama-cabang');

            // hide semua
            alamatKontak.classList.add('d-none');
            namaCabang.classList.add('d-none');

            // role yang butuh alamat & kontak
            if (role === 'Cabang' || role === 'Supplier') {
                alamatKontak.classList.remove('d-none');
            }

            // role khusus cabang
            if (role === 'Cabang') {
                namaCabang.classList.remove('d-none');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.querySelector('select[name="role"]');

            toggleRoleFields(roleSelect.value);

            roleSelect.addEventListener('change', function() {
                toggleRoleFields(this.value);
            });
        });
    </script>
@endsection
