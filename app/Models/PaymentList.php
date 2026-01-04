<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentList extends Model
{
    use HasFactory;

    protected $table = 'payment_lists';

    protected $fillable = [
        'name',
        'description',
        'photo',
        'created_by',
        'created_role',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // User yang membuat list pembayaran
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
