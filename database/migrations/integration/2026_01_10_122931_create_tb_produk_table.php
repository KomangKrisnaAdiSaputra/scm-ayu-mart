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
        Schema::connection('mysqlIntegration')->create('tb_produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->integer('id_jenis');
            $table->string('kode_produk');
            $table->string('nama_produk');
            $table->string('deskripsi_produk')->nullable();
            $table->decimal('harga_produk', 10, 0);
            $table->decimal('harga_beli', 10, 0);
            $table->boolean('is_diskon_active')->default(0);
            $table->decimal('harga_diskon', 10, 0)->nullable();
            $table->date('tanggal_mulai_diskon')->nullable();
            $table->date('tanggal_akhir_diskon')->nullable();
            $table->string('berat_produk')->nullable();
            $table->string('foto_produk')->nullable();
            $table->enum('status_produk', ['aktif', 'nonaktif']);
            $table->string('satuan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysqlIntegration')->dropIfExists('tb_produk');
    }
};
