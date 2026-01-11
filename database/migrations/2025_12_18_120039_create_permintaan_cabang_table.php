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
        Schema::create('permintaan_cabang', function (Blueprint $table) {
            $table->id('permintaan_id');
            $table->unsignedBigInteger('cabang_id');
            $table->dateTime('tanggal_permintaan');
            $table->enum('status_permintaan', ['Menunggu', 'Diterima', 'Ditolak']);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_cabang');
    }
};
