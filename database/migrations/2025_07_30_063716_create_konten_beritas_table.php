<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    // database/migrations/xxxx_xx_xx_create_konten_beritas_table.php
    Schema::create('konten_beritas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('berita_id')->constrained('beritas')->onDelete('cascade');
        $table->enum('tipe', ['teks', 'gambar']);
        $table->text('konten'); // path gambar atau isi teks
        $table->integer('urutan');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('konten_beritas');
    }
};
