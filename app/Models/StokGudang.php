<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokGudang extends Model
{
    protected $table = 'stok_gudang';
    protected $primaryKey = 'stok_id';

    protected $fillable = [
        'produk_id',
        'stok_total',
        'stok_minimum'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
