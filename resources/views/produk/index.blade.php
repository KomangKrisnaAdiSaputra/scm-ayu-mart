@extends('layouts.app')
@section('titlePage', 'Manajemen Produk')

@php
    $roleManajer = auth()->user()->role == 'Manajer';
@endphp

@section('css')
    @if (!$roleManajer)
        <style>
            .harga-beli {
                display: none;
            }
        </style>
    @endif

@endsection

@section('app')
    {{-- <h2 class="section-title">Table</h2>
    <p class="section-lead">Example of some Bootstrap table components.</p> --}}
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            @if (auth()->user()->role == 'Manajer')
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('produk.form') }}"" class="btn btn-primary">Tambah Data</a>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Data Produk</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('produk') }}">
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
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Kategori / Satuan</th>
                                @if ($roleManajer)
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                @else
                                    <th>Harga</th>
                                @endif
                                @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Cabang']))
                                    <th>Stok</th>
                                @endif
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            @foreach ($produk as $key => $item)
                                @php
                                    $stokGudang = $stok[$item->id_produk] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->kode_produk }}</td>
                                    <td>{{ $item->nama_produk }}</td>
                                    <td>{{ $item->jenis?->nama_jenis }} / {{ $item->satuan }}</td>
                                    @if ($roleManajer)
                                        <td>{{ $item->harga_beli }}</td>
                                    @endif
                                    <td>{{ $item->harga_produk }}</td>
                                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
                                        <td>
                                            @php
                                                $stokTotal = $stokGudang?->stok_total ?? 0;
                                                $stokMinimum = $stokGudang?->stok_minimum ?? 0;
                                            @endphp

                                            @if ($stokTotal < $stokMinimum)
                                                <div class="text-danger font-weight-bold">
                                                    🔴 {{ $stokTotal }} / {{ $stokMinimum }}
                                                </div>
                                                <small class="text-danger">
                                                    Stok menipis
                                                </small>
                                            @else
                                                <div class="text-success font-weight-bold">
                                                    🟢 {{ $stokTotal }}
                                                </div>
                                            @endif
                                        </td>
                                    @elseif (in_array(auth()->user()->role, ['Cabang']))
                                        <td>
                                            @php
                                                $stokCabang = $item?->stok_cabangs->first() ?? null;
                                                $stokTotal = $stokCabang?->total_stok ?? 0;
                                                $stokMinimum = $stokCabang?->stok_minimum ?? 0;
                                            @endphp

                                            @if ($stokTotal < $stokMinimum)
                                                <div class="text-danger font-weight-bold">
                                                    🔴 {{ $stokTotal }} / {{ $stokMinimum }}
                                                </div>
                                                <small class="text-danger">
                                                    Stok menipis
                                                </small>
                                            @else
                                                <div class="text-success font-weight-bold">
                                                    🟢 {{ $stokTotal }}
                                                </div>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        <div
                                            class="badge  {{ $item->status_produk == 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $item->status_produk }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#modalDetailProduk" data-produk='@json($item)'
                                            data-stok-gudang='@json($stokGudang)'>
                                            Detail
                                        </button>

                                        @if ($roleManajer)
                                            <a href="{{ route('produk.form', ['id' => $item->id_produk]) }}"
                                                class="btn btn-secondary btn-sm mr-1">
                                                Edit
                                            </a>

                                            <form action="{{ route('produk.delete', $item->id_produk) }}" method="POST"
                                                class="d-inline" onsubmit="return confirmDelete()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </form>
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
        function confirmDelete() {
            if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                return false;
            }

            return confirm('Data yang dihapus TIDAK BISA dikembalikan. Lanjutkan?');
        }

        $('#modalDetailProduk').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget)
            const produk = button.data('produk')
            const stokGudang = button.data('stok-gudang')

            /* ===== PRODUK ===== */
            $('#d_kode').text(produk.kode_produk ?? '-')
            $('#d_nama').text(produk.nama_produk ?? '-')
            $('#d_kategori').text(produk.jenis?.nama_jenis ?? '-')
            $('#d_satuan').text(produk.satuan ?? '-')
            $('#d_berat').text(produk.berat_produk ? produk.berat_produk + ' Kg' : '-')
            $('#d_deskripsi').text(produk.deskripsi_produk ?? '-')

            /* ===== HARGA ===== */
            $('#d_harga_beli').text(
                'Rp ' + Number(produk.harga_beli ?? 0).toLocaleString('id-ID')
            )
            $('#d_harga_jual').text(
                'Rp ' + Number(produk.harga_produk ?? 0).toLocaleString('id-ID')
            )

            /* ===== DISKON ===== */
            if (produk.is_diskon_active && produk.harga_diskon) {
                $('#row_diskon').removeClass('d-none')
                $('#d_diskon').html(`
            Rp ${Number(produk.harga_diskon).toLocaleString('id-ID')}
            <br>
            <small class="text-muted">
                ${produk.tanggal_mulai_diskon} s/d ${produk.tanggal_akhir_diskon}
            </small>
        `)
            } else {
                $('#row_diskon').addClass('d-none')
            }

            /* ===== STATUS ===== */
            $('#d_status')
                .text(produk.status_produk)
                .removeClass('badge-success badge-danger')
                .addClass(produk.status_produk === 'aktif' ?
                    'badge-success' :
                    'badge-danger')

            /* ===== STOK GUDANG ===== */
            if (stokGudang) {
                const total = Number(stokGudang.stok_total ?? 0)
                const minimum = Number(stokGudang.stok_minimum ?? 0)

                if (total < minimum) {
                    $('#d_stok_gudang').html(
                        `<span class="text-danger font-weight-bold">
                    🔴 ${total} / ${minimum}
                </span>`
                    )
                    $('#d_stok_warning')
                        .text('Stok menipis')
                        .addClass('text-danger')
                } else {
                    $('#d_stok_gudang').html(
                        `<span class="text-success font-weight-bold">
                    🟢 ${total}
                </span>`
                    )
                    $('#d_stok_warning').text('').removeClass('text-danger')
                }
            }

            /* ===== STOK CABANG ===== */
            const list = $('#d_stok_cabang')
            list.empty()

            if (produk.stok_cabangs && produk.stok_cabangs.length) {
                produk.stok_cabangs.forEach(stok => {
                    const danger = stok.total_stok <= stok.stok_minimum
                    list.append(`
                <li class="${danger ? 'text-danger font-weight-bold' : ''}">
                    ${stok.cabang?.nama_cabang ?? 'Cabang'} :
                    ${stok.total_stok} (Min: ${stok.stok_minimum})
                </li>
            `)
                })
            } else {
                list.append('<li>- Tidak ada data stok cabang -</li>')
            }
        })
    </script>

@endsection


{{-- Modal Detail Produk --}}
<div class="modal fade" id="modalDetailProduk" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Produk</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <table class="table table-sm table-borderless">

                    {{-- INFORMASI PRODUK --}}
                    <tr>
                        <th width="35%">Kode Produk</th>
                        <td id="d_kode"></td>
                    </tr>
                    <tr>
                        <th>Nama Produk</th>
                        <td id="d_nama"></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td id="d_kategori"></td>
                    </tr>
                    <tr>
                        <th>Satuan</th>
                        <td id="d_satuan"></td>
                    </tr>
                    <tr>
                        <th>Berat</th>
                        <td id="d_berat"></td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td id="d_deskripsi"></td>
                    </tr>

                    {{-- HARGA --}}
                    <tr class="table-secondary">
                        <th colspan="2">Harga</th>
                    </tr>
                    @if (in_array(auth()->user()->role, ['Manajer']))
                        <tr>
                            <th>Harga Beli</th>
                            <td id="d_harga_beli"></td>
                        </tr>
                    @endif
                    <tr>
                        <th>{{ in_array(auth()->user()->role, ['Manajer']) ? 'Harga Jual' : 'Harga Produk' }}</th>
                        <td id="d_harga_jual"></td>
                    </tr>
                    <tr id="row_diskon" class="table-warning d-none">
                        <th>Harga Diskon</th>
                        <td id="d_diskon"></td>
                    </tr>

                    {{-- STATUS --}}
                    <tr>
                        <th>Status</th>
                        <td><span id="d_status" class="badge"></span></td>
                    </tr>

                    {{-- STOK GUDANG --}}
                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang']))
                        <tr class="table-secondary">
                            <th colspan="2">Stok Gudang Pusat</th>
                        </tr>
                        <tr>
                            <th>Total / Minimum</th>
                            <td id="d_stok_gudang"></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <small id="d_stok_warning"></small>
                            </td>
                        </tr>
                    @endif

                    {{-- STOK CABANG --}}
                    <tr class="table-secondary">
                        <th colspan="2">Stok Per Cabang</th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <ul id="d_stok_cabang" class="mb-0 pl-3"></ul>
                        </td>
                    </tr>

                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
