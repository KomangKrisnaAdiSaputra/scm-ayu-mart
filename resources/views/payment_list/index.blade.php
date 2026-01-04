@extends('layouts.app')
@section('titlePage', 'Payment List')
@section('app')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="d-flex justify-content-end mb-3">
                @if (auth()->user()->role === 'Manajer' || auth()->user()->role === 'Supplier')
                    <a href="{{ route('paymentlist.form') }}" class="btn btn-primary">
                        Tambah Payment List
                    </a>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Payment List</h4>
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
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                @if (auth()->user()->role == 'Manajer')
                                    <th>Created By</th>
                                @endif
                                <th>Action</th>
                            </tr>

                            @forelse ($paymentLists as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        @if ($item->photo)
                                            <img src="{{ asset($item->photo) }}" alt="photo"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>{{ $item->name }}</td>

                                    <td>
                                        {{ \Illuminate\Support\Str::limit($item->description, 50, '...') }}
                                    </td>

                                    @if (auth()->user()->role == 'Manajer')
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $item->creator->nama }}
                                            </span>
                                        </td>
                                    @endif

                                    <td class="text-nowrap">
                                        {{-- Detail --}}
                                        <button class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#modalDetailPayment" data-name="{{ $item->name }}"
                                            data-description="{{ $item->description }}" data-photo="{{ $item->photo }}"
                                            data-role="{{ $item->created_role }}"
                                            data-supplier="{{ $item->supplier->nama_supplier ?? '-' }}">
                                            Detail
                                        </button>

                                        {{-- Edit --}}
                                        @if (auth()->user()->role === 'Manajer' ||
                                                (auth()->user()->role === 'Supplier' && $item->supplier_id === auth()->user()->supplier_id))
                                            <a href="{{ route('paymentlist.form', $item->id) }}"
                                                class="btn btn-secondary btn-sm">
                                                Edit
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Data payment list belum tersedia
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
        $('#modalDetailPayment').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);

            let name = button.data('name') ?? '-';
            let description = button.data('description') ?? '-';
            let photo = button.data('photo');
            let role = button.data('role') ?? '-';
            let supplier = button.data('supplier') ?? '-';

            let modal = $(this);

            modal.find('#detailName').text(name);
            modal.find('#detailDescription').text(description || '-');
            modal.find('#detailRole').text(role);
            modal.find('#detailSupplier').text(supplier);

            if (photo) {
                modal.find('#detailPhoto').attr('src', "{{ asset('') }}" + photo).show();
            } else {
                modal.find('#detailPhoto').hide();
            }
        });
    </script>
@endsection

<!-- Modal Detail Payment -->
<div class="modal fade" id="modalDetailPayment" tabindex="-1" role="dialog" aria-labelledby="modalDetailPaymentLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailPaymentLabel">Detail Payment List</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">

                    {{-- FOTO --}}
                    <div class="col-md-4 text-center">
                        <img id="detailPhoto" src="" class="img-fluid rounded mb-3"
                            style="max-height:200px;object-fit:cover">
                    </div>

                    {{-- INFO --}}
                    <div class="col-md-8">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="30%">Nama</th>
                                <td id="detailName">-</td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td id="detailDescription">-</td>
                            </tr>
                            <tr>
                                <th>Dibuat Oleh</th>
                                <td>
                                    <span class="badge badge-info" id="detailRole">-</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td id="detailSupplier">-</td>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
