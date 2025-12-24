<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';
    protected $primaryKey = 'pengiriman_id';

    protected $fillable = [
        'permintaan_id',
        'tanggal_kirim',
        'status_pengiriman'
    ];

    public $timestamps = false;

    public function permintaan()
    {
        return $this->belongsTo(PermintaanCabang::class, 'permintaan_id');
    }

    public function status_kurir()
    {
        return $this->hasOne(StatusKurir::class, 'pengiriman_id');
    }
}
