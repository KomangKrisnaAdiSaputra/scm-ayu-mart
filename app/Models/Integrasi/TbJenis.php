<?php

namespace App\Models\Integrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbJenis extends Model
{
    use HasFactory;
    protected $connection = 'mysqlIntegration';

    protected $table = 'tb_jenis';
    protected $primaryKey = 'id_jenis';

    protected $fillable = [
        'nama_jenis',
        'deskripsi_jenis'
    ];
}
