@extends('layouts.app')
@section('titlePage', 'Retur')
@php
    $breadcrumbs = [
        ['label' => 'Retur', 'url' => route('retur'), 'active' => 'active'],
        ['label' => 'Form Retur', 'url' => '', 'active' => ''],
    ];
@endphp
@section('app')
    <div class="card">
        <div class="card-header">
            <h4>Buat Retur Barang</h4>
        </div>

        <div class="card-body">

            <div class="form-group">
                <label>Pilih Purchase Order</label>
                <select class="form-control" id="poSelector">
                    <option value="">-- Pilih PO --</option>
                    @foreach ($poList as $po)
                        <option value="{{ $po->po_id }}">
                            {{ $po->kode_po }} | {{ $po->supplier->nama_supplier }}
                        </option>
                    @endforeach
                </select>
            </div>

            <form method="POST" action="{{ route('retur.store') }}">
                @csrf

                @foreach ($poList as $po)
                    <div class="po-wrapper" id="po-{{ $po->po_id }}" style="display:none">

                        <input type="hidden" name="po_id" value="{{ $po->po_id }}">

                        <h6 class="mb-2">
                            PO: {{ $po->kode_po }}
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Pilih</th>
                                        <th>Produk</th>
                                        <th>Qty Terima</th>
                                        <th>Qty Retur</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($po->detail as $i => $item)
                                        @php
                                            $produk = $allProduk->where('id_produk', $item->produk_id)->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="check-retur"
                                                    data-row="{{ $po->po_id }}-{{ $i }}"
                                                    name="items[{{ $po->po_id }}][{{ $i }}][checked]"
                                                    value="1">

                                                <input type="hidden"
                                                    name="items[{{ $po->po_id }}][{{ $i }}][produk_id]"
                                                    value="{{ $item->produk_id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $produk->nama_produk }}</strong><br>
                                                <small>{{ $produk->kode_produk }}</small>
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                <input type="number" class="form-control qty-retur"
                                                    data-row="{{ $po->po_id }}-{{ $i }}"
                                                    name="items[{{ $po->po_id }}][{{ $i }}][qty_retur]"
                                                    min="1" max="{{ $item->qty }}" disabled>

                                            </td>
                                            <td>
                                                <input type="text" class="form-control alasan-retur"
                                                    data-row="{{ $po->po_id }}-{{ $i }}"
                                                    name="items[{{ $po->po_id }}][{{ $i }}][alasan]"
                                                    maxlength="150" disabled>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <button class="btn btn-danger mt-3" id="btnSubmit" style="display:none">
                    Simpan Retur
                </button>

            </form>

        </div>
    </div>
@endsection

@section('js')
    <script>
        document.getElementById('poSelector').addEventListener('change', function() {
            document.querySelectorAll('.po-wrapper').forEach(el => el.style.display = 'none');
            document.getElementById('btnSubmit').style.display = 'none';

            if (this.value) {
                document.getElementById('po-' + this.value).style.display = 'block';
                document.getElementById('btnSubmit').style.display = 'inline-block';
            }
        });

        document.querySelectorAll('.check-retur').forEach(cb => {
            cb.addEventListener('change', function() {

                const row = this.dataset.row;
                const qty = document.querySelector(`.qty-retur[data-row="${row}"]`);
                const alasan = document.querySelector(`.alasan-retur[data-row="${row}"]`);

                if (this.checked) {
                    qty.disabled = false;
                    alasan.disabled = false;

                    qty.required = true;
                    alasan.required = true;
                } else {
                    qty.disabled = true;
                    alasan.disabled = true;

                    qty.required = false;
                    alasan.required = false;

                    qty.value = '';
                    alasan.value = '';
                }
            });
        });
    </script>

@endsection
