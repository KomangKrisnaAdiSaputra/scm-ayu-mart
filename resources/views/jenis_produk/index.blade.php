@extends('layouts.app')
@section('titlePage', 'Jenis Produk')

@section('app')

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="d-flex justify-content-end mb-3">
                @if (auth()->user()->role === 'Manajer')
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        + Tambah Jenis Produk
                    </button>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Jenis</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('paymentlist') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search payment...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary custom-search-button" type="submit"
                                        style="border-radius: 0 30px 30px 0 !important;">
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
                                <th>Nama Jenis</th>
                                <th>Deskripsi</th>
                                <th>Action</th>
                            </tr>

                            @forelse ($jenisProduk as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->nama_jenis }}</td>
                                    <td>{{ $item->deskripsi_jenis ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" data-id="{{ $item->id_jenis }}"
                                            data-nama="{{ $item->nama_jenis }}"
                                            data-deskripsi="{{ $item->deskripsi_jenis }}">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        Data jenis produk belum tersedia
                                    </td>
                                </tr>
                            @endforelse

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        function openCreateModal() {
            $('#modalTitle').text('Tambah Jenis Produk');

            $('#jenis_id').val('');
            $('#nama_jenis').val('');
            $('#deskripsi').val('');

            $('#modalJenisProduk').modal('show');
        }

        $('.btn-edit').on('click', function() {
            $('#modalTitle').text('Edit Jenis Produk');

            $('#jenis_id').val($(this).data('id'));
            $('#nama_jenis').val($(this).data('nama'));
            $('#deskripsi').val($(this).data('deskripsi') ?? '');

            $('#modalJenisProduk').modal('show');
        });
    </script>
@endsection

<div class="modal fade" id="modalJenisProduk" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form action="{{ route('jenisproduk.save') }}" method="POST" class="modal-content">
            @csrf

            {{-- hidden id --}}
            <input type="hidden" name="id" id="jenis_id">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Jenis Produk</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>Nama Jenis</label>
                    <input type="text" name="nama_jenis" id="nama_jenis" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi_jenis" id="deskripsi" class="form-control" rows="3"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>
