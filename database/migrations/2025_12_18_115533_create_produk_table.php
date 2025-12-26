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
        Schema::create('produk', function (Blueprint $table) {
            $table->id('produk_id');
            $table->string('kode_produk', 50);
            $table->string('nama_produk', 50);
            $table->string('kategori', 30);
            $table->string('satuan', 20);
            $table->integer('harga_beli');
            $table->integer('harga_jual');
            $table->enum('status_produk', ['aktif', 'nonaktif']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
