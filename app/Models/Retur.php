<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $table = 'retur';
    protected $primaryKey = 'retur_id';

    protected $fillable = [
        'po_id',
        'produk_id',
        'qty_retur',
        'alasan',
        'tanggal_retur'
    ];
}
