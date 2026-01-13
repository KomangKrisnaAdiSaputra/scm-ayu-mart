<?php

namespace App\Http\Controllers;

use App\Models\PaymentList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentListController extends Controller
{
    function index(Request $request)
    {
        $user = auth()->user();

        $query = PaymentList::when($request->search, function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        })->where("created_role", auth()->user()->role);

        if (auth()->user()->role == "Supplier") {
            $query = $query->where("created_by", auth()->user()->users_id);
        }
        $paymentLists = $query->latest()->get();
        return view('payment_list.index', compact('paymentLists'));
    }

    public function form($id = null)
    {
        $user = auth()->user();

        // Hanya Manajer & Supplier
        if (!in_array($user->role, ['Manajer', 'Supplier'])) {
            abort(403);
        }

        $paymentList = null;

        // Mode EDIT
        if ($id) {
            $paymentList = PaymentList::findOrFail($id);

            // Supplier hanya boleh edit data miliknya sendiri
            if (
                $user->role === 'Supplier' &&
                $paymentList->supplier_id !== $user->supplier_id
            ) {
                abort(403);
            }
        }

        return view('payment_list.form', compact('paymentList'));
    }

    public function save(Request $request, $id = null)
    {
        $user = auth()->user();

        // Role yang diizinkan
        if (!in_array($user->role, ['Manajer', 'Supplier'])) {
            abort(403);
        }

        // VALIDASI
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {

            // MODE UPDATE
            if ($id) {
                $paymentList = PaymentList::findOrFail($id);

                // Supplier hanya boleh update miliknya sendiri
                if (
                    $user->role === 'Supplier' &&
                    $paymentList->supplier_id !== $user->supplier_id
                ) {
                    abort(403);
                }
            }
            // MODE CREATE
            else {
                $paymentList = new PaymentList();
                $paymentList->created_by   = $user->users_id;
                $paymentList->created_role = $user->role;
            }

            // DATA UTAMA
            $paymentList->name = $validated['name'];
            $paymentList->description = $validated['description'] ?? null;

            // UPLOAD FOTO KE PUBLIC
            if ($request->hasFile('photo')) {

                $folder = public_path('uploads/payment-list');

                // Buat folder jika belum ada
                if (!file_exists($folder)) {
                    mkdir($folder, 0755, true);
                }

                // Hapus foto lama (jika update)
                if ($id && $paymentList->photo && file_exists(public_path($paymentList->photo))) {
                    unlink(public_path($paymentList->photo));
                }

                $file = $request->file('photo');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                $file->move($folder, $filename);

                // Simpan path RELATIF
                $paymentList->photo = 'uploads/payment-list/' . $filename;
            }

            $paymentList->save();

            DB::commit();

            return redirect()
                ->route('paymentlist')
                ->with('success', 'Payment list berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        $payment = PaymentList::findOrFail($id);

        // Optional: hapus foto jika ada
        if ($payment->photo && file_exists(public_path($payment->photo))) {
            unlink(public_path($payment->photo));
        }

        $payment->delete();

        return redirect()
            ->route('paymentlist')
            ->with('success', 'Payment list berhasil dihapus.');
    }
}
