@extends('layouts.app')

@section('app')
    <div class="row">
        <div class="col-12 col-lg-12 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-cog"></i> User Settings
                    </h4>
                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('setting.user.update') }}">
                        @csrf

                        <div class="row">

                            {{-- ===================== LEFT ===================== --}}
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user"></i> Informasi Akun
                                </h6>

                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="nama" class="form-control"
                                        value="{{ old('nama', $user->nama) }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control"
                                        value="{{ old('username', $user->username) }}" required readonly>
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>

                            {{-- ===================== RIGHT ===================== --}}
                            <div class="col-md-6">
                                <h6 class="text-warning mb-3">
                                    <i class="fas fa-lock"></i> Keamanan
                                </h6>

                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" name="password" class="form-control">
                                    <small class="text-muted">
                                        Kosongkan jika tidak ingin mengubah
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>

                        </div>

                        <hr>

                        <div class="text-right">
                            <button class="btn btn-primary px-4">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
