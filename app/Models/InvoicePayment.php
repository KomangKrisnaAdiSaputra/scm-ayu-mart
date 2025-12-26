<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'invoice_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_bayar',
        'bukti_pembayaran',
        'catatan_manajer',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    // ================= RELATION =================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    // ================= HELPER =================

    public function buktiUrl()
    {
        return $this->bukti_pembayaran
            ? asset('storage/' . $this->bukti_pembayaran)
            : null;
    }
}
