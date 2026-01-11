<?php

namespace App\Models\Integrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbCabang extends Model
{
    use HasFactory;
    protected $connection = 'mysqlIntegration';

    protected $table = 'tb_cabang';
    protected $primaryKey = 'id_cabang';

    protected $fillable = [
        'users_id',
        'nama_cabang',
        'alamat',
        'kontak'
    ];
}
