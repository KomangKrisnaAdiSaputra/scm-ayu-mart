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
        Schema::create('detail_purchase_order', function (Blueprint $table) {
            $table->id('po_dtl_id');
            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('qty');
            $table->integer('harga');

            $table->foreign('po_id')->references('po_id')->on('purchase_order');
            $table->foreign('produk_id')->references('produk_id')->on('produk');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_purchase_order');
    }
};
