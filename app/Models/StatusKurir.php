<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusKurir extends Model
{
    protected $table = 'status_kurir';
    protected $primaryKey = 'status_id';

    protected $fillable = [
        'pengiriman_id',
        'status_kurir',
        'waktu_update',
        'catatan',
        'nama_kurir'
    ];

    public $timestamps = false;

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }
}
