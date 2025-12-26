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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->unsignedBigInteger('po_id');
            $table->string('nomor_invoice')->unique();
            $table->date('tanggal_invoice');
            $table->decimal('total_invoice', 15, 2);
            $table->enum('status_invoice', [
                'Menunggu Pembayaran',
                'Lunas',
                'Ditolak'
            ])->default('Menunggu Pembayaran');
            $table->text('catatan_supplier')->nullable();
            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('purchase_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
