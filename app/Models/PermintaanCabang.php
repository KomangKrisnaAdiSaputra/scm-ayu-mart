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

    public $timestamps = false;

    public function detail()
    {
        return $this->hasMany(DetailPermintaanCabang::class, 'permintaan_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id', 'cabang_id');
    }
}
