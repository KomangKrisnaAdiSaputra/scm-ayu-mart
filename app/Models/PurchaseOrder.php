<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'supplier_id',
        'tanggal_po',
        'total_po',
        'status_po',
        'status_pembayaran',
        'tanggal_pembayaran'
    ];

    public $timestamps = false;

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function detail()
    {
        return $this->hasMany(DetailPurchaseOrder::class, 'po_id');
    }

    public function scopeByRole($q, $role)
    {
        if ($role === 'Gudang') {
            $q->whereIn('status_po', ['Draft', 'Menunggu Persetujuan']);
        } elseif ($role === 'Manajer') {
            $q->where('status_po', 'Menunggu Persetujuan');
        } elseif ($role === 'Supplier') {
            $q->where('status_po', 'Disetujui Manajer')
                ->where('supplier_id', auth()->user()->supplier_id);
        } else {
            $q->whereNotIn('status_po', [
                'Draft',
                'Menunggu Persetujuan'
            ]);
        }
    }
}
