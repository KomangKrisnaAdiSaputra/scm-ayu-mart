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
        Schema::connection('mysqlIntegration')->create('tb_stok_cabang', function (Blueprint $table) {
            $table->id('id_stok_cabang');
            $table->string('id_produk');
            $table->string('id_cabang');
            $table->integer('total_stok');
            $table->integer('stok_minimum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysqlIntegration')->dropIfExists('tb_stok_cabang');
    }
};
