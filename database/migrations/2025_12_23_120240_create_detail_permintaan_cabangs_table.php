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
        Schema::create('detail_permintaan_cabang', function (Blueprint $table) {
            $table->id('detail_id');
            $table->unsignedBigInteger('permintaan_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('qty_permintaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_permintaan_cabangs');
    }
};
