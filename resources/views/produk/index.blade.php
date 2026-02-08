@extends('layouts.app')
@section('titlePage', 'Manajemen Produk')

@php
    $roleManajer = in_array(auth()->user()->role, ['Manajer']);
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
                                @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Cabang', 'Purchasing']))
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
                                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Purchasing']))
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

                                        @if (in_array(auth()->user()->role, ['Cabang']) && $item->stok_cabangs->count() > 0)
                                            <button class="btn btn-sm btn-warning btn-edit-stok-minimum"
                                                data-stokcabang='@json($item->stok_cabangs)'
                                                data-namaproduk="{{ $item->nama_produk }}">
                                                Edit Stok Minimum
                                            </button>
                                        @endif

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

                                        @if (in_array(auth()->user()->role, ['Cabang', 'Gudang']))
                                            <button class="btn btn-sm btn-warning btn-edit-stok"
                                                data-stokcabang='@json($item->stok_cabangs->first())'
                                                data-stokgudang='@json($stok[$item->id_produk])'
                                                data-idproduk='@json($item->id_produk)'
                                                data-namaproduk="{{ $item->nama_produk }}">
                                                Edit Stok
                                            </button>
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
        window.routes = {
            stokCabang: "{{ route('produk.stokcabang', ':id') }}",
        };

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

        $(document).on('click', '.btn-edit-stok-minimum', function() {
            const stokCabang = JSON.parse($(this).attr('data-stokcabang'));
            const namaProduk = $(this).attr('data-namaproduk');

            $('#stok_minimum').val(stokCabang.stok_minimum);

            $('#editStokTitle').text(
                'Edit Stok Minimum - ' + namaProduk
            );

            $('#editStokForm').attr(
                'action',
                window.routes.stokCabang.replace(':id', stokCabang.id_stok_cabang)
            );

            $('#detailModal').modal('show');
        });

        $('.btn-edit-stok').on('click', function() {
            const stokCabang = $(this).data('stokcabang');
            const stokGudang = $(this).data('stokgudang');
            const namaProduk = $(this).data('namaproduk');
            const idProduk = $(this).data('idproduk');
            const role = "{{ auth()->user()->role }}";
            const riwayatStok = @json($riwayatStok);
            console.log(stokCabang, idProduk, riwayatStok, stokGudang);

            $('#namaProduk').text(namaProduk);
            $('#produkId').val(idProduk);

            let stokTotal = 0;
            let stokMinimum = 0;

            if (role === 'Cabang' && stokCabang) {
                stokTotal = stokCabang.total_stok ?? 0;
                stokMinimum = stokCabang.stok_minimum ?? 0;
            }

            if (role === 'Gudang' && stokGudang) {
                stokTotal = stokGudang.stok_total ?? 0;
                stokMinimum = stokGudang.stok_minimum ?? 0;
            }

            // ===== tampilkan stok =====
            $('#stokTerkini').text(stokTotal);
            $('#stokMinimum').text(stokMinimum);

            if (stokTotal < stokMinimum) {
                $('#statusStok')
                    .html('🔴 <b>Stok menipis</b>')
                    .removeClass('text-success')
                    .addClass('text-danger');
            } else {
                $('#statusStok')
                    .html('🟢 <b>Stok aman</b>')
                    .removeClass('text-danger')
                    .addClass('text-success');
            }

            // ===== riwayat =====

            let html = '';

            riwayatStok.forEach(r => {

                const tanggal = new Date(r.created_at).toLocaleString('id-ID', {
                    dateStyle: 'short',
                    timeStyle: 'short'
                });

                const badge = r.type === 'gudang' ?
                    'primary' :
                    'info';

                html += `
        <tr>
            <td class="text-nowrap">${tanggal}</td>
            <td>
                <div class="font-weight-bold">${r.nama}</div>
                <small class="text-muted">oleh ${r.nama_user}</small>
            </td>
            <td class="text-center">
                <span class="text-muted">${r.qty_lama}</span>
                <i class="fa fa-arrow-right mx-1 text-secondary"></i>
                <b>${r.qty_baru}</b>
            </td>
            <td>
                ${r.keterangan ?? '<span class="text-muted">-</span>'}
            </td>
        </tr>
    `;
            });

            $('#riwayatStokBody').html(
                html || `<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat</td></tr>`
            );


            $('#modalEditStok').modal('show');
        });
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
                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Purchasing']))
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

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" id="editStokForm">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStokTitle">
                        Edit Stok Minimum
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Stok Minimum</label>
                        <input type="number" name="stok_minimum" id="stok_minimum" class="form-control"
                            min="0" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditStok" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    Edit Stok – <span id="namaProduk"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form method="POST" action="{{ route('produk.updatestok') }}">
                @csrf

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">

                            <!-- INFO STOK TERKINI -->
                            <div class="card mb-3">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small class="text-muted">Stok Terkini</small>
                                            <h5 class="mb-0 font-weight-bold" id="stokTerkini">-</h5>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-muted">Stok Minimum</small>
                                            <div id="stokMinimum">-</div>
                                        </div>
                                    </div>
                                    <div id="statusStok" class="mt-1 small"></div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <input type="hidden" name="produk_id" id="produkId">

                            <div class="form-group">
                                <label>Stok Baru</label>
                                <input type="number" class="form-control" name="stok_baru" required>
                            </div>

                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="3"></textarea>
                            </div>
                        </div>


                        <!-- RIWAYAT -->
                        <div class="col-md-9 border-left">
                            <h6 class="font-weight-bold mb-2">Riwayat Stok</h6>

                            <div class="table-responsive" style="max-height:300px">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%; text-align: center;">Tanggal</th>
                                            <th style="width: 25%;">Nama</th>
                                            <th style="width: 25%; text-align: center;">Perubahan</th>
                                            <th style="width: 30%;">Keterangan</th>
                                        </tr>
                                    </thead>


                                    <tbody id="riwayatStokBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Pilih produk
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-warning">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>
