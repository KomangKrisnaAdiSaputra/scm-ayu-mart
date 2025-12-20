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
        Schema::create('penerimaan_produk', function (Blueprint $table) {
            $table->id('penerimaan_id');
            $table->unsignedBigInteger('po_id');
            $table->dateTime('tanggal_terima');
            $table->integer('total_diterima');
            $table->enum('status_penerimaan',['Lengkap','Kurang','Retur']);

            $table->foreign('po_id')->references('po_id')->on('purchase_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_produk');
    }
};
