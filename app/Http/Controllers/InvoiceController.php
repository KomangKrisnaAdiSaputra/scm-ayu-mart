<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Buat invoice dari PO
    public function createFromPo($poId)
    {
        $po = PurchaseOrder::with('invoice')->findOrFail($poId);

        // ❌ Jika invoice sudah ada
        if ($po->invoice) {
            return back()->with('error', 'Invoice untuk PO ini sudah dibuat');
        }

        DB::transaction(function () use ($po) {

            Invoice::create([
                'po_id'          => $po->po_id,
                'nomor_invoice'  => 'INV-' . $po->po_id . '-' . date('YmdHis'),
                'tanggal_invoice' => now(),
                'total_invoice'  => $po->total_po,
                'status_invoice' => 'Menunggu Pembayaran'
            ]);

            // Update status pembayaran PO
            $po->update([
                'status_pembayaran' => 'Belum Bayar'
            ]);
        });

        return back()->with('success', 'Invoice berhasil dibuat');
    }

    // Upload / update pembayaran
    public function savePayment(Request $request, $invoiceId)
    {
        $invoice = Invoice::with('po', 'payment')->findOrFail($invoiceId);

        // ❌ Jika sudah lunas
        if ($invoice->status_invoice === 'Lunas') {
            return back()->with('error', 'Invoice sudah lunas');
        }

        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date|before_or_equal:today',
            'metode_bayar'      => 'required|string|max:50',
            'bukti_pembayaran'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::transaction(function () use ($request, $invoice) {

            // upload file (jika ada)
            $path = $request->hasFile('bukti_pembayaran')
                ? $request->file('bukti_pembayaran')
                ->store('bukti-pembayaran', 'public')
                : optional($invoice->payment)->bukti_pembayaran;

            InvoicePayment::updateOrCreate(
                ['invoice_id' => $invoice->invoice_id],
                [
                    'tanggal_bayar'    => $request->tanggal_bayar,
                    'jumlah_bayar'     => $request->jumlah_bayar,
                    'metode_bayar'     => $request->metode_bayar,
                    'catatan_manajer'  => $request->catatan_manajer,
                    'bukti_pembayaran' => $path,
                ]
            );

            // Update invoice
            $invoice->update([
                'status_invoice' => 'Lunas',
                'alasan_ditolak' => null
            ]);

            // Update PO
            $invoice->po->update([
                'status_pembayaran' => 'Sudah Bayar'
            ]);
        });

        return back()->with('success', 'Pembayaran invoice berhasil disimpan');
    }

    // Tolak invoice
    public function reject(Request $request, $invoiceId)
    {
        $request->validate([
            'alasan_ditolak' => 'required'
        ]);

        Invoice::where('invoice_id', $invoiceId)->update([
            'status_invoice' => 'Ditolak',
            'alasan_ditolak' => $request->alasan_ditolak
        ]);

        return back()->with('success', 'Invoice ditolak');
    }
}
