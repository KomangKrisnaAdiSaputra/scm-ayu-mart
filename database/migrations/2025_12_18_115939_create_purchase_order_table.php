<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id('po_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('kode_po')->unique();
            $table->dateTime('tanggal_po');
            $table->integer('total_po')->nullable();
            $table->enum('status_po', [
                'Draft',
                'Menunggu Persetujuan',
                'Disetujui Purchasing',
                'Ditolak Purchasing',
                'Disetujui Manajer',
                'Ditolak Manajer',
                'Diterima Supplier',
                'Ditolak Supplier',
                'Dikirim Supplier',
                'Selesai',
                'Retur'
            ]);
            $table->enum('status_pembayaran', ['Belum Bayar', 'Sudah Bayar']);
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('supplier_id')->references('supplier_id')->on('supplier');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order');
    }
};
