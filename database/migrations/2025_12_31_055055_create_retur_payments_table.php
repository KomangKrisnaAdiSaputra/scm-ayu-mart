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
        Schema::create('retur_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('retur_id');
            $table->unsignedBigInteger('po_id');

            $table->string('metode_pembayaran', 50)->nullable();
            $table->decimal('jumlah', 15, 2);

            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->string('bukti_pembayaran')->nullable();

            $table->string('status', 30)->default('Pending');
            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_payments');
    }
};
