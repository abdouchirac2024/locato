<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_logements', function (Blueprint $table) {
            $table->id();
            $table->string('urlImage');
            $table->float('taille');
            $table->boolean('is_principale')->default(false);
            $table->string('titre')->nullable();
            $table->string('titre_en')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('logId');
            $table->foreign('logId')->references('id')->on('logements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_logements');
    }
};
