<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'produk_id';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'kategori',
        'satuan',
        'harga_beli',
        'harga_jual',
        'status_produk'
    ];

    public function stok()
    {
        return $this->hasOne(StokGudang::class, 'produk_id');
    }
}
