@extends('layouts.app')
@section('titlePage', 'Purchase Order')

@php
    $breadcrumbs = [
        ['label' => 'Purchase Order', 'url' => route('purchaseorder'), 'active' => 'active'],
        ['label' => 'Form Permintaan', 'url' => '', 'active' => ''],
    ];
@endphp
@section('app')
    <h2 class="section-title">Form Purchase</h2>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">

                <form method="POST"
                    action="{{ $po ? route('purchaseorder.update', $po->po_id) : route('purchaseorder.store') }}">
                    @csrf
                    @if ($po)
                        @method('PUT')
                    @endif

                    <div class="card-header">
                        <h4>{{ $po ? 'Edit Purchase Order' : 'Buat Purchase Order' }}</h4>
                    </div>

                    <div class="card-body">

                        {{-- SUPPLIER --}}
                        <div class="form-group">
                            <label>Supplier</label>
                            <select name="supplier_id" class="form-control" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($supplier as $s)
                                    <option value="{{ $s->supplier_id }}"
                                        {{ old('supplier_id', $po->supplier_id ?? '') == $s->supplier_id ? 'selected' : '' }}>
                                        {{ $s->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan', $po->catatan ?? '') }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PRODUK --}}
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="45%">Produk</th>
                                        <th width="15%">Qty</th>
                                        <th width="20%">Harga</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="produk-wrapper">
                                    @forelse ($details as $i => $d)
                                        <tr>
                                            <td>
                                                <select name="produk[{{ $i }}][id_produk]"
                                                    class="form-control produk-select" required>
                                                    @foreach ($produk as $p)
                                                        <option value="{{ $p->id_produk }}"
                                                            data-harga="{{ $p->harga_beli }}"
                                                            {{ $d->id_produk == $p->id_produk ? 'selected' : '' }}>
                                                            {{ $p->nama_produk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="number" name="produk[{{ $i }}][qty]"
                                                    class="form-control" min="1" value="{{ $d->qty }}"
                                                    required>
                                            </td>

                                            <td>
                                                {{-- INPUT TAMPILAN --}}
                                                <input type="text" class="form-control harga-format text-right"
                                                    value="{{ number_format($d->harga, 0, ',', '.') }}" autocomplete="off">

                                                {{-- INPUT ASLI --}}
                                                <input type="hidden" name="produk[{{ $i }}][harga]"
                                                    class="harga-asli" value="{{ $d->harga }}">
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td>
                                                <select name="produk[0][id_produk]" class="form-control produk-select"
                                                    required>
                                                    <option value="">-- Pilih Produk --</option>
                                                    @foreach ($produk as $p)
                                                        <option value="{{ $p->id_produk }}"
                                                            data-harga="{{ $p->harga_beli }}">
                                                            {{ $p->nama_produk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="number" name="produk[0][qty]" class="form-control"
                                                    min="1" value="1">
                                            </td>

                                            <td>
                                                <input type="text" class="form-control harga-format text-right"
                                                    autocomplete="off" readonly>
                                                <input type="hidden" name="produk[0][harga]" class="harga-asli">
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>

                        <button type="button" class="btn btn-success btn-sm" id="add-row">
                            + Tambah Produk
                        </button>

                    </div>

                    <div class="card-footer text-right">
                        <button class="btn btn-primary">
                            {{ $po ? 'Update PO' : 'Simpan PO' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        let index = {{ count($details) ?: 1 }};

        $('#add-row').click(function() {
            let row = `
    <tr>
        <td>
            <select name="produk[${index}][id_produk]" class="form-control produk-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach ($produk as $p)
                    <option value="{{ $p->id_produk }}" data-harga="{{ $p->harga_beli }}">
                        {{ $p->nama_produk }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="number" name="produk[${index}][qty]" class="form-control" min="1" value="1">
        </td>

        <td>
            <input type="text" class="form-control harga-format text-right" autocomplete="off">
            <input type="hidden" name="produk[${index}][harga]" class="harga-asli">
        </td>

        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
        </td>
    </tr>`;

            $('#produk-wrapper').append(row);
            index++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '.produk-select', function() {
            let harga = $(this).find(':selected').data('harga') || 0;
            let row = $(this).closest('tr');

            row.find('.harga-format').val(formatRupiah(harga));
            row.find('.harga-asli').val(harga);
        });

        function formatRupiah(angka) {
            angka = angka.toString().replace(/[^,\d]/g, '');
            let split = angka.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return rupiah;
        }

        function cleanNumber(str) {
            return str.replace(/\./g, '');
        }
    </script>

@endsection
