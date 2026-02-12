<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'kode_po',
        'supplier_id',
        'tanggal_po',
        'total_po',
        'status_po',
        'status_pembayaran',
        'tanggal_pembayaran',
        'catatan',
        'catatan_privasi',
    ];

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'po_id');
    }

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
            $q->whereIn('status_po', [
                'Draft',
                'Menunggu Persetujuan',
                'Disetujui Purchasing',
                'Ditolak Purchasing',
                'Disetujui Manajer',
                'Ditolak Manajer',
                'Diterima Supplier',
                'Ditolak Supplier',
                'Dikirim Supplier',
                'Retur',
                'Selesai'
            ]);
        } else if ($role === 'Purchasing') {
            $q->whereIn('status_po', [
                'Menunggu Persetujuan',
                'Disetujui Purchasing',
                'Ditolak Purchasing',
                'Disetujui Manajer',
                'Ditolak Manajer',
                'Diterima Supplier',
                'Ditolak Supplier',
                'Dikirim Supplier',
                'Selesai',
                'Retur',
            ]);
        } else if ($role === 'Manajer') {
            $q->whereIn('status_po', [
                'Disetujui Purchasing',
                'Disetujui Manajer',
                'Ditolak Manajer',
                'Diterima Supplier',
                'Ditolak Supplier',
                'Dikirim Supplier',
                'Selesai',
                'Retur',
            ]);
        } elseif ($role === 'Supplier') {
            $q->whereIn('status_po', [
                'Disetujui Manajer',
                'Diterima Supplier',
                'Ditolak Supplier',
                'Dikirim Supplier',
                'Retur',
                'Selesai'
            ])->where('supplier_id', auth()->user()->supplier->supplier_id);
        } else {
            $q->whereNotIn('status_po', [
                'Draft',
                'Menunggu Persetujuan'
            ]);
        }
    }
}
