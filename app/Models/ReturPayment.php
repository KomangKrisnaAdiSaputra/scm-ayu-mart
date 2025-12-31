<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPayment extends Model
{
    use HasFactory;

    protected $table = 'retur_payments';

    protected $fillable = [
        'retur_id',
        'po_id',
        'metode_pembayaran',
        'jumlah',
        'tanggal_pembayaran',
        'bukti_pembayaran',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
        'jumlah' => 'decimal:2',
    ];

    /* ================= RELATIONS ================= */

    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
