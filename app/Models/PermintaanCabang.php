<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanCabang extends Model
{
    protected $table = 'permintaan_cabang';
    protected $primaryKey = 'permintaan_id';

    protected $fillable = [
        'cabang_id',
        'tanggal_permintaan',
        'status_permintaan'
    ];

    public function detail()
    {
        return $this->hasMany(DetailPermintaanCabang::class, 'permintaan_id');
    }
}
