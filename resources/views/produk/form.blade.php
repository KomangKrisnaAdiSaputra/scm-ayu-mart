@extends('layouts.app')
@section('titlePage', 'Manajemen Produk')

@section('app')
    <h2 class="section-title">Form Produk</h2>
    <p class="section-lead">
        {{ isset($produk->id_produk) ? 'Edit' : 'Tambah' }} Data Produk
    </p>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <form method="POST" action="{{ route('produk.save', $produk->id_produk ?? null) }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- ================= HEADER ================= --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Produk</h4>

                        <div class="d-flex align-items-center">
                            <label class="mr-3 mb-0 font-weight-bold">Status:</label>

                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_aktif" name="status_produk" value="aktif"
                                    class="custom-control-input"
                                    {{ old('status_produk', $produk->status_produk ?? 'aktif') == 'aktif' ? 'checked' : '' }}>
                                <label class="custom-control-label text-success" for="status_aktif">Aktif</label>
                            </div>

                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="status_nonaktif" name="status_produk" value="nonaktif"
                                    class="custom-control-input"
                                    {{ old('status_produk', $produk->status_produk ?? '') == 'nonaktif' ? 'checked' : '' }}>
                                <label class="custom-control-label text-danger" for="status_nonaktif">Nonaktif</label>
                            </div>
                        </div>
                    </div>

                    {{-- ================= BODY ================= --}}
                    <div class="card-body">
                        <div class="row">

                            {{-- KIRI --}}
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Kode Produk</label>
                                    <input type="text" name="kode_produk" class="form-control"
                                        value="{{ old('kode_produk', $produk->kode_produk ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" name="nama_produk" class="form-control"
                                        value="{{ old('nama_produk', $produk->nama_produk ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label>Jenis Produk</label>
                                    <select name="kategori" class="form-control">
                                        <option value="">-- Pilih Jenis --</option>
                                        @foreach ($jenis as $j)
                                            <option value="{{ $j->id_jenis }}"
                                                {{ old('kategori', $produk->id_jenis ?? '') == $j->id_jenis ? 'selected' : '' }}>
                                                {{ $j->nama_jenis }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Deskripsi Produk</label>
                                    <textarea name="deskripsi_produk" rows="4" class="form-control">{{ old('deskripsi_produk', $produk->deskripsi_produk ?? '') }}</textarea>
                                </div>

                            </div>

                            {{-- KANAN --}}
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Satuan</label>
                                    <input type="text" name="satuan" class="form-control"
                                        value="{{ old('satuan', $produk->satuan ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label>Harga Beli</label>
                                    <input type="text" name="harga_beli" class="form-control text-right currency"
                                        value="{{ old('harga_beli', $produk->harga_beli ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label>Harga Jual</label>
                                    <input type="text" name="harga_jual" class="form-control text-right currency"
                                        value="{{ old('harga_jual', $produk->harga_produk ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label>Berat Produk</label>
                                    <input type="text" name="berat_produk" class="form-control"
                                        value="{{ old('berat_produk', $produk->berat_produk ?? '') }}"
                                        placeholder="contoh: 500 gram">
                                </div>

                                <div class="form-group">
                                    <label>Foto Produk</label>
                                    <input type="file" name="foto_produk" class="form-control-file">

                                    @if (!empty($produk->foto_produk))
                                        <img src="{{ $produk->foto_produk }}" class="mt-2" height="80">
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ================= DISKON ================= --}}
                    <div class="card-body border-top">
                        <h5>Diskon Produk</h5>

                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="diskon_switch" name="is_diskon_active"
                                value="1"
                                {{ old('is_diskon_active', $produk->is_diskon_active ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="diskon_switch">Aktifkan Diskon</label>
                        </div>

                        <div id="diskon_fields">
                            <div class="form-group">
                                <label>Harga Diskon</label>
                                <input type="text" name="harga_diskon" class="form-control text-right currency"
                                    value="{{ old('harga_diskon', $produk->harga_diskon ?? '') }}">
                            </div>

                            <div class="form-group">
                                <label>Tanggal Mulai Diskon</label>
                                <input type="date" name="tanggal_mulai_diskon" class="form-control"
                                    value="{{ old('tanggal_mulai_diskon', $produk->tanggal_mulai_diskon ?? '') }}">
                            </div>

                            <div class="form-group">
                                <label>Tanggal Akhir Diskon</label>
                                <input type="date" name="tanggal_akhir_diskon" class="form-control"
                                    value="{{ old('tanggal_akhir_diskon', $produk->tanggal_akhir_diskon ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- ================= STOK ================= --}}
                    <div class="card-body border-top">
                        <h5>Stok Gudang</h5>
                        <div class="row">

                            @if (!isset($produk->id_produk))
                                <div class="col-md-6">
                                    <label>Stok Awal</label>
                                    <input type="number" name="stok_total" class="form-control" min="0">
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label>Stok Minimum</label>
                                <input type="number" name="stok_minimum" class="form-control"
                                    value="{{ old('stok_minimum', $produk->stok->stok_minimum ?? 0) }}">
                            </div>

                        </div>
                    </div>

                    {{-- ================= FOOTER ================= --}}
                    <div class="card-footer text-right">
                        <a href="{{ route('produk') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-primary">
                            {{ isset($produk->id_produk) ? 'Update' : 'Simpan' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function formatRupiah(val) {
            return val.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        document.querySelectorAll('.currency').forEach(input => {
            if (input.value) input.value = formatRupiah(input.value);
            input.addEventListener('input', () => input.value = formatRupiah(input.value));
            input.form.addEventListener('submit', () => {
                input.value = input.value.replace(/\./g, '');
            });
        });

        const sw = document.getElementById('diskon_switch');
        const df = document.getElementById('diskon_fields');

        function toggleDiskon() {
            df.style.display = sw.checked ? 'block' : 'none';
        }
        sw.addEventListener('change', toggleDiskon);
        toggleDiskon();
    </script>
@endsection
