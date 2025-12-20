<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('manajer.supplier.index', [
            'supplier' => Supplier::all()
        ]);
    }

    public function store(Request $request)
    {
        Supplier::create($request->validate([
            'users_id' => 'required',
            'nama_supplier' => 'required',
            'alamat' => 'required',
            'kontak' => 'required',
            'status_supplier' => 'required'
        ]));

        return back()->with('success','Supplier berhasil ditambahkan');
    }
}
