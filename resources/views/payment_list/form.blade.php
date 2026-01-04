@extends('layouts.app')
@section('titlePage', 'Payment List')

@php
    $breadcrumbs = [
        ['label' => 'Payment List', 'url' => route('paymentlist'), 'active' => 'active'],
        ['label' => isset($paymentList) ? 'Edit Payment' : 'Tambah Payment', 'url' => '', 'active' => ''],
    ];
@endphp

@section('app')
    <h2 class="section-title">Form Payment List</h2>
    <p class="section-lead">
        {{ isset($paymentList) ? 'Edit' : 'Tambah' }} Payment List
    </p>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">

                <form method="POST" action="{{ route('paymentlist.save', $paymentList->id ?? null) }}"
                    enctype="multipart/form-data">

                    @csrf

                    {{-- HEADER --}}
                    <div class="card-header">
                        <h4>Payment List</h4>
                    </div>

                    {{-- BODY --}}
                    <div class="card-body">
                        <div class="row">

                            {{-- KIRI --}}
                            <div class="col-md-6">

                                {{-- NAMA --}}
                                <div class="form-group">
                                    <label>Nama Payment</label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $paymentList->name ?? '') }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- DESKRIPSI --}}
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $paymentList->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            {{-- KANAN --}}
                            <div class="col-md-6">

                                {{-- FOTO --}}
                                <div class="form-group">
                                    <label>Foto</label>
                                    <input type="file" name="photo"
                                        class="form-control @error('photo') is-invalid @enderror">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- PREVIEW FOTO (EDIT) --}}
                                @if (isset($paymentList) && $paymentList->photo)
                                    <div class="form-group">
                                        <label>Foto Saat Ini</label><br>
                                        <img src="{{ asset($paymentList->photo) }}" alt="photo"
                                            style="width:120px;height:120px;object-fit:cover;border-radius:8px">
                                    </div>
                                @endif

                            </div>

                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="card-footer text-right">
                        <a href="{{ route('paymentlist') }}" class="btn btn-secondary mr-2">
                            Batal
                        </a>
                        <button class="btn btn-primary">
                            {{ isset($paymentList) ? 'Update' : 'Simpan' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
