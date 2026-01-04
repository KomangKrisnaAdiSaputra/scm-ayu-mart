<?php

namespace App\Http\Controllers;

use App\Models\PaymentList;
use App\Models\PurchaseOrder;
use App\Models\Retur;
use App\Models\ReturPayment;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $retur = Retur::with([
            'produk',
            'purchaseOrder',
            'tb_payment'
        ])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('produk', function ($qp) use ($search) {
                    $qp->where('nama_produk', 'like', "%$search%")
                        ->orWhere('kode_produk', 'like', "%$search%");
                })
                    ->orWhereHas('purchaseOrder', function ($qp) use ($search) {
                        $qp->where('kode_po', 'like', "%$search%");
                    })
                    ->orWhere('status_retur', 'like', "%$search%");
            })
            ->orderBy('tanggal_retur', 'desc')
            ->paginate(10)
            ->withQueryString();

        $paymentLists = PaymentList::select(["name", "description", "photo", "created_by"])->where("created_role", "Manajer")->get();

        return view('retur.index', compact('retur', 'paymentLists'));
    }

    function create()
    {
        $poList = PurchaseOrder::where('status_po', 'Dikirim Supplier')
            ->orderBy('tanggal_po', 'desc')
            ->get();

        return view('retur.create', compact('poList'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $hasRetur = false;

            foreach ($request->items as $poId => $products) {

                foreach ($products as $item) {

                    if (!isset($item['checked'])) continue;

                    // ❌ Jika dicentang tapi qty / alasan kosong
                    if (
                        empty($item['qty_retur']) ||
                        empty($item['alasan'])
                    ) {
                        throw new \Exception(
                            'Qty retur dan alasan wajib diisi untuk produk yang dipilih'
                        );
                    }

                    if ($item['qty_retur'] <= 0) {
                        throw new \Exception('Qty retur harus lebih dari 0');
                    }

                    Retur::create([
                        'po_id'         => $poId,
                        'produk_id'     => $item['produk_id'],
                        'qty_retur'     => $item['qty_retur'],
                        'alasan'        => $item['alasan'],
                        'tanggal_retur' => now(),
                        'status_retur'  => 'Menunggu Konfirmasi',
                    ]);

                    $hasRetur = true;
                }

                if ($hasRetur) {
                    PurchaseOrder::where('po_id', $poId)
                        ->update(['status_po' => 'Retur']);
                }
            }

            DB::commit();

            return redirect()->route('retur')
                ->with('success', 'Retur berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function terimaRetur(Request $request, $id)
    {
        $request->validate([
            'jenis_retur' => 'required|in:dana,barang',
        ]);

        $retur = Retur::findOrFail($id);

        abort_if(auth()->user()->role !== 'Supplier', 403);
        abort_if($retur->status_retur !== 'Menunggu Konfirmasi', 400);

        if ($request->jenis_retur === 'dana') {
            $retur->update([
                'status_retur' => "Diterima",
                'payment' => 1,
                'catatan' => $request->catatan
            ]);
        } else {
            $retur->update([
                'status_retur' => "Diterima",
                'payment' => 0,
                'catatan' => $request->catatan
            ]);
        }

        return back()->with('success', 'Retur berhasil diterima');
    }

    public function storeReturPayment(Request $request)
    {
        abort_if(auth()->user()->role !== 'Manajer', 403);

        $request->validate([
            'retur_id' => 'required|exists:retur,retur_id',
            'jumlah' => 'required|numeric|min:1',
            // 'metode_pembayaran' => 'required',
        ]);

        $retur = Retur::findOrFail($request->retur_id);
        abort_if($retur->payment !== 1, 400);

        ReturPayment::create([
            'retur_id' => $retur->retur_id,
            'po_id' => $retur->po_id,
            'jumlah' => $request->jumlah,
            // 'metode_pembayaran' => $request->metode_pembayaran,
            // 'tanggal_pembayaran' => now(),
            'status' => 'Menunggu Pembayaran',
            'created_by' => auth()->user()->users_id,
        ]);

        $retur->update([
            'status_retur' => 'Menunggu Pembayaran'
        ]);

        return back()->with('success', 'Retur payment berhasil dibuat');
    }

    public function bayar(Request $request)
    {
        $request->validate([
            'retur_id' => 'required|exists:retur,retur_id',
            'metode_pembayaran' => 'required',
            'jumlah' => 'required|numeric',
            'tanggal_pembayaran' => 'required|date',
            'bukti_pembayaran' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $payment = ReturPayment::where('retur_id', $request->retur_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status !== 'Menunggu Pembayaran') {
                throw new \Exception('Pembayaran sudah diproses');
            }

            $bukti = $payment->bukti_pembayaran;

            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');

                $filename = 'refund_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('returpayment/refund'), $filename);

                $bukti = 'returpayment/refund/' . $filename;
            }

            $payment->update([
                'metode_pembayaran' => $request->metode_pembayaran,
                'jumlah' => $request->jumlah,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'bukti_pembayaran' => $bukti,
                'keterangan' => $request->keterangan,
                'status' => 'Sudah Dibayar',
                'created_by' => auth()->user()->users_id,
            ]);

            Retur::where('retur_id', $request->retur_id)
                ->update(['status_retur' => 'Dibayar']);

            DB::commit();

            return back()->with('success', 'Refund berhasil dibayar');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function kirimBarang($id)
    {
        abort_if(auth()->user()->role !== 'Supplier', 403);

        $retur = Retur::findOrFail($id);

        abort_if($retur->payment !== 0, 400);

        $retur->update([
            'status_retur' => 'Dikirim'
        ]);

        return back()->with('success', 'Barang retur telah dikirim');
    }

    public function selesai($id)
    {
        abort_if(
            !in_array(auth()->user()->role, ['Gudang', 'Manajer']),
            403
        );

        $retur = Retur::findOrFail($id);

        // abort_if($retur->status_retur !== 'Dikirim Supplier', 400);

        // OPTIONAL: tambah stok
        // StokGudang::increment(...);

        $poUpdate = [];
        $po = $retur->purchaseOrder;

        if ($retur->status_retur == "Dikirim") {
            // Ambil detail PO produk terkait
            $poDetail = $po->detail()
                ->where('produk_id', $retur->produk_id)
                ->lockForUpdate()
                ->first();

            if (!$poDetail) {
                throw new \Exception('Detail PO tidak ditemukan');
            }

            $qtyPo    = $poDetail->qty;
            $qtyRetur = $retur->qty_retur;

            if ($qtyRetur > $qtyPo) {
                throw new \Exception('Qty retur melebihi qty PO');
            }

            /**
             * 1️⃣ Hitung qty masuk gudang
             */
            $qtyMasukGudang = $qtyPo - $qtyRetur;

            /**
             * 2️⃣ Tambah stok gudang
             */
            $stok = StokGudang::where('produk_id', $retur->produk_id)
                ->lockForUpdate()
                ->first();

            if (!$stok) {
                throw new \Exception('Stok gudang tidak ditemukan');
            }

            $stok->increment('stok_total', $qtyMasukGudang);

            /**
             * 3️⃣ Update qty PO detail
             */
            $poDetail->update([
                'qty' => $qtyMasukGudang
            ]);

            /**
             * 4️⃣ Hitung ulang TOTAL PO
             */
            $totalPoBaru = $po->detail->sum(function ($item) {
                return $item->qty * $item->harga;
            });

            $poUpdate['total_po'] = $totalPoBaru;
        }

        $po->update([
            ...$poUpdate,
            'status_po' => 'Retur'
        ]);
        $retur->update([
            'status_retur' => 'Selesai'
        ]);


        return back()->with('success', 'Retur barang success');
    }


    // public function terima($id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $retur = Retur::with(['purchaseOrder.detail', 'produk'])
    //             ->lockForUpdate()
    //             ->findOrFail($id);

    //         if ($retur->status_retur !== 'Menunggu Konfirmasi') {
    //             throw new \Exception('Retur sudah diproses');
    //         }

    //         $po = $retur->purchaseOrder;

    //         // Ambil detail PO produk terkait
    //         $poDetail = $po->detail()
    //             ->where('produk_id', $retur->produk_id)
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$poDetail) {
    //             throw new \Exception('Detail PO tidak ditemukan');
    //         }

    //         $qtyPo    = $poDetail->qty;
    //         $qtyRetur = $retur->qty_retur;

    //         if ($qtyRetur > $qtyPo) {
    //             throw new \Exception('Qty retur melebihi qty PO');
    //         }

    //         /**
    //          * 1️⃣ Hitung qty masuk gudang
    //          */
    //         $qtyMasukGudang = $qtyPo - $qtyRetur;

    //         /**
    //          * 2️⃣ Tambah stok gudang
    //          */
    //         $stok = StokGudang::where('produk_id', $retur->produk_id)
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$stok) {
    //             throw new \Exception('Stok gudang tidak ditemukan');
    //         }

    //         $stok->increment('stok_total', $qtyMasukGudang);

    //         /**
    //          * 3️⃣ Update qty PO detail
    //          */
    //         $poDetail->update([
    //             'qty' => $qtyMasukGudang
    //         ]);

    //         /**
    //          * 4️⃣ Hitung ulang TOTAL PO
    //          */
    //         $totalPoBaru = $po->detail->sum(function ($item) {
    //             return $item->qty * $item->harga;
    //         });

    //         $po->update([
    //             'total_po' => $totalPoBaru,
    //             'status_po' => 'Retur'
    //         ]);

    //         /**
    //          * 5️⃣ Update status retur
    //          */
    //         $retur->update([
    //             'status_retur' => 'Diterima'
    //         ]);

    //         DB::commit();

    //         return back()->with('success', 'Retur diterima, stok & total PO diperbarui');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', $e->getMessage());
    //     }
    // }


    public function tolak($id)
    {
        DB::beginTransaction();

        try {
            $retur = Retur::with('purchaseOrder')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($retur->status_retur !== 'Menunggu Konfirmasi') {
                throw new \Exception('Retur sudah diproses');
            }

            $retur->update([
                'status_retur' => 'Ditolak'
            ]);

            // PO kembali ke kondisi sebelumnya
            $retur->purchaseOrder->update([
                'status_po' => 'Dikirim Supplier'
            ]);

            DB::commit();

            return back()->with('success', 'Retur ditolak');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
