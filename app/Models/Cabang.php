<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $table = 'cabang';
    protected $primaryKey = 'cabang_id';

    protected $fillable = [
        'users_id',
        'nama_cabang',
        'alamat',
        'kontak'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
