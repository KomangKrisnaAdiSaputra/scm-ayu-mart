<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'po_id',
        'nomor_invoice',
        'tanggal_invoice',
        'total_invoice',
        'status_invoice',
        'alasan_ditolak',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
    ];

    // ================= RELATION =================

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    public function payment()
    {
        return $this->hasOne(InvoicePayment::class, 'invoice_id', 'invoice_id');
    }

    // ================= HELPER =================

    public function isPaid()
    {
        return $this->status_invoice === 'Lunas';
    }

    public function canUploadPayment()
    {
        return in_array($this->status_invoice, [
            'Menunggu Pembayaran',
            'Ditolak'
        ]);
    }
}
