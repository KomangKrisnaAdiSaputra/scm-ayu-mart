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
        Schema::create('supplier', function (Blueprint $table) {
            $table->id('supplier_id');
            $table->unsignedBigInteger('users_id');
            $table->string('nama_supplier',50);
            $table->string('alamat',100);
            $table->string('kontak',20);
            $table->enum('status_supplier',['aktif','nonaktif']);

            $table->foreign('users_id')->references('users_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
