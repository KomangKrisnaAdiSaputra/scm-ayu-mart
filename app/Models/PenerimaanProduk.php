<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanProduk extends Model
{
    protected $table = 'penerimaan_produk';
    protected $primaryKey = 'penerimaan_id';

    protected $fillable = [
        'po_id',
        'tanggal_terima',
        'total_diterima',
        'status_penerimaan'
    ];

    public $timestamps = false;

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
}
