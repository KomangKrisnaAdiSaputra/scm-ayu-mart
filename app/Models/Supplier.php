<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'users_id',
        'nama_supplier',
        'alamat',
        'kontak',
        'status_supplier'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
