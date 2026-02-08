<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatStok extends Model
{
    use HasFactory;

    protected $table = 'riwayat_stok';

    protected $fillable = [
        'produk_id',
        'type',
        'nama',
        'nama_user',
        'qty_lama',
        'qty_baru',
        'keterangan',
    ];
}
