<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPermintaanCabang extends Model
{
    use HasFactory;

    protected $table = 'detail_permintaan_cabang';

    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'permintaan_id',
        'produk_id',
        'qty_permintaan',
    ];

    // Relasi ke permintaan
    public function permintaan()
    {
        return $this->belongsTo(PermintaanCabang::class, 'permintaan_id', 'permintaan_id');
    }
}
