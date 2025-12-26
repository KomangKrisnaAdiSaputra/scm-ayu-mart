<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPurchaseOrder extends Model
{
    protected $table = 'detail_purchase_order';
    protected $primaryKey = 'po_dtl_id';

    protected $fillable = [
        'po_id',
        'produk_id',
        'qty',
        'harga'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
