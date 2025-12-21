@extends('layouts.app')
@section('titlePage', 'Manajemen Produk')

@php
    $breadcrumbs = [
        ['label' => 'Manajemen Produk', 'url' => route('produk'), 'active' => 'active'],
        ['label' => isset($id) && $id ? 'Edit Produk' : 'Tambah Produk', 'url' => '', 'active' => ''],
    ];
@endphp
@section('app')
    <h2 class="section-title">Form Produk</h2>
    <p class="section-lead">{{ isset($produk->produk_id) && $produk->produk_id ? 'Edit' : 'Tambah' }} Data Produk</p>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <form method="POST" action="{{ route('produk.save', $produk->produk_id ?? null) }}">
                    @csrf

                    <div class="card">

                        {{-- HEADER --}}
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Produk</h4>

                            {{-- STATUS RADIO --}}
                            <div class="d-flex align-items-center">
                                <label class="mr-3 mb-0 font-weight-bold">Status:</label>

                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="status_aktif" name="status_produk" value="aktif"
                                        class="custom-control-input"
                                        {{ old('status_produk', $produk->status_produk ?? 'aktif') == 'aktif' ? 'checked' : '' }}>
                                    <label class="custom-control-label text-success" for="status_aktif">
                                        Aktif
                                    </label>
                                </div>

                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="status_nonaktif" name="status_produk" value="nonaktif"
                                        class="custom-control-input"
                                        {{ old('status_produk', $produk->status_produk ?? '') == 'nonaktif' ? 'checked' : '' }}>
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
                                        <label>Kode Produk</label>
                                        <input type="text" name="kode_produk"
                                            class="form-control @error('kode_produk') is-invalid @enderror"
                                            value="{{ old('kode_produk', $produk->kode_produk ?? '') }}">
                                        @error('kode_produk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Nama Produk</label>
                                        <input type="text" name="nama_produk"
                                            class="form-control @error('nama_produk') is-invalid @enderror"
                                            value="{{ old('nama_produk', $produk->nama_produk ?? '') }}">
                                        @error('nama_produk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <input type="text" name="kategori"
                                            class="form-control @error('kategori') is-invalid @enderror"
                                            value="{{ old('kategori', $produk->kategori ?? '') }}">
                                        @error('kategori')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>

                                {{-- KANAN --}}
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <input type="text" name="satuan"
                                            class="form-control @error('satuan') is-invalid @enderror"
                                            value="{{ old('satuan', $produk->satuan ?? '') }}">
                                        @error('satuan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Harga Beli</label>
                                        <input type="text" name="harga_beli"
                                            class="form-control text-right currency @error('harga_beli') is-invalid @enderror"
                                            value="{{ old('harga_beli', $produk->harga_beli ?? '') }}">
                                        @error('harga_beli')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Harga Jual</label>
                                        <input type="text" name="harga_jual"
                                            class="form-control text-right currency @error('harga_jual') is-invalid @enderror"
                                            value="{{ old('harga_jual', $produk->harga_jual ?? '') }}">
                                        @error('harga_jual')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER --}}
                        <div class="card-footer text-right">
                            <a href="{{ route('produk') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button class="btn btn-primary">
                                {{ isset($produk) ? 'Update' : 'Simpan' }}
                            </button>
                        </div>

                    </div>
                </form>


            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function formatRupiah(angka) {
            return angka.replace(/\D/g, '')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        document.querySelectorAll('.currency').forEach(function(input) {

            // Format saat load (edit mode)
            if (input.value) {
                input.value = formatRupiah(input.value);
            }

            // Format saat mengetik
            input.addEventListener('input', function() {
                this.value = formatRupiah(this.value);
            });

            // Bersihkan sebelum submit
            input.closest('form').addEventListener('submit', function() {
                input.value = input.value.replace(/\./g, '');
            });
        });
    </script>

@endsection
