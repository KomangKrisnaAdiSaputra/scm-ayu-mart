<?php

namespace App\Models\Integrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbProduk extends Model
{
    use HasFactory;
    protected $connection = 'mysqlIntegration';

    protected $table = 'tb_produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_jenis',
        'kode_produk',
        'nama_produk',
        'deskripsi_produk',
        'harga_produk',
        'harga_beli',
        'is_diskon_active',
        'harga_diskon',
        'tanggal_mulai_diskon',
        'tanggal_akhir_diskon',
        'berat_produk',
        'foto_produk',
        'status_produk',
        'satuan',
    ];

    function jenis()
    {
        return $this->hasOne(TbJenis::class, 'id_jenis', 'id_jenis');
    }

    function stok_cabangs()
    {
        return $this->hasMany(TbStokCabang::class, 'id_produk', 'id_produk');
    }
}
