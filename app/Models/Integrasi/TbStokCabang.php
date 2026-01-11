<?php

namespace App\Models\Integrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbStokCabang extends Model
{
    use HasFactory;
    protected $connection = 'mysqlIntegration';

    protected $table = 'tb_stok_cabang';
    protected $primaryKey = 'id_stok_cabang';

    protected $fillable = [
        'id_produk',
        'id_cabang',
        'total_stok',
        'stok_minimum',
    ];
}
