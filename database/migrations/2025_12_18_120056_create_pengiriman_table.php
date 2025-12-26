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
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id('pengiriman_id');
            $table->unsignedBigInteger('permintaan_id');
            $table->dateTime('tanggal_kirim');
            $table->enum('status_pengiriman', ['Diproses', 'Dikirim', 'Diterima']);

            $table->foreign('permintaan_id')->references('permintaan_id')->on('permintaan_cabang');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
