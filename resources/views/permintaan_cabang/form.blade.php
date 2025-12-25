@extends('layouts.app')
@section('titlePage', 'Permintaan Cabang')

@php
    $breadcrumbs = [
        ['label' => 'Pemintaan Cabang', 'url' => route('permintaancabang'), 'active' => 'active'],
        ['label' => 'Form Permintaan', 'url' => '', 'active' => ''],
    ];
@endphp
@section('app')
    <h2 class="section-title">Form Produk</h2>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <form method="POST" action="{{ route('permintaancabang.store') }}">
                    @csrf

                    <div class="card">
                        <div class="card-header">
                            <h4>Permintaan Produk Cabang</h4>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th style="min-width:200px">Produk</th>
                                            <th class="text-center" style="min-width:90px">Qty</th>
                                            <th class="text-center" style="min-width:60px">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody id="produk-wrapper">
                                        <tr>
                                            <td>
                                                <select name="produk[0][produk_id]" class="form-control"
                                                    style="min-width:180px" required>
                                                    <option value="">-- Pilih Produk --</option>
                                                    @foreach ($produk as $item)
                                                        <option value="{{ $item->produk_id }}">
                                                            {{ $item->nama_produk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="number" name="produk[0][qty]" class="form-control text-center"
                                                    min="1" inputmode="numeric" style="min-width:90px" required>
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>


                            <button type="button" class="btn btn-success btn-sm" id="add-row">
                                + Tambah Produk
                            </button>

                        </div>

                        <div class="card-footer text-right">
                            <button class="btn btn-primary">Kirim Permintaan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let index = 1;

        document.getElementById('add-row').addEventListener('click', function() {
            let html = `
        <tr>
            <td>
                <select name="produk[${index}][produk_id]" class="form-control" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach ($produk as $item)
                        <option value="{{ $item->produk_id }}">{{ $item->nama_produk }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="produk[${index}][qty]" class="form-control" min="1" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </td>
        </tr>`;
            document.getElementById('produk-wrapper').insertAdjacentHTML('beforeend', html);
            index++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endsection
