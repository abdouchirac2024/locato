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
        Schema::create('image_logements', function (Blueprint $table) {
            $table->id();
            $table->binary('urlImage');
            $table->float('taille');
            $table->timestamps();
            $table->unsignedBigInteger('logId'); // id logement
            $table->foreign('logId')->references('id')->on('logements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_logements');
    }
};
