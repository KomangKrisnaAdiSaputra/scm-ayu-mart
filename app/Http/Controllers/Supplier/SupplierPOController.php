<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SupplierPOController extends Controller
{
    public function index()
    {
        return view('supplier.po.index', [
            'po' => PurchaseOrder::where('supplier_id', auth()->user()->users_id)->get()
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        PurchaseOrder::where('po_id',$id)->update([
            'status_po' => $request->status_po
        ]);

        return back()->with('success','Status PO diperbarui');
    }
}
