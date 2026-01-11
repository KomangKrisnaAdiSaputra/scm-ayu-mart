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
        Schema::create('retur', function (Blueprint $table) {
            $table->id('retur_id');
            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('qty_retur');
            $table->string('alasan', 150);
            $table->dateTime('tanggal_retur');
            $table->enum('status_retur', ['Menunggu Konfirmasi', 'Diterima', 'Ditolak', 'Dikirim', 'Selesai', 'Menunggu Pembayaran', 'Dibayar'])->default('Menunggu Konfirmasi');
            $table->boolean('payment')->default(0);
            $table->text('catatan')->nullable();

            $table->foreign('po_id')->references('po_id')->on('purchase_order');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur');
    }
};
