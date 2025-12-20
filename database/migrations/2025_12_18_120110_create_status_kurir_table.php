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
        Schema::create('status_kurir', function (Blueprint $table) {
            $table->id('status_id');
            $table->unsignedBigInteger('pengiriman_id');
            $table->enum('status_kurir',['Dalam Pengiriman','Terkirim','Gagal']);
            $table->dateTime('waktu_update');
            $table->string('catatan',150)->nullable();
            $table->string('nama_kurir',50);

            $table->foreign('pengiriman_id')->references('pengiriman_id')->on('pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_kurir');
    }
};
