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
        Schema::connection('mysqlIntegration')->create('tb_cabang', function (Blueprint $table) {
            $table->id('id_cabang');
            $table->unsignedBigInteger('users_id');
            $table->string('nama_cabang', 50);
            $table->string('alamat', 100);
            $table->string('kontak', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysqlIntegration')->dropIfExists('tb_cabang');
    }
};
