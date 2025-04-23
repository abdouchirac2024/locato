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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('contenu_fr');
            $table->dateTime('dateEnv');// date d'envoie
            $table->dateTime('dateLect')->nullable(); // date de lecture
            $table->boolean('lecture')->nullable();
            $table->timestamps();
            // migration
            $table->unsignedBigInteger('locaId');
            $table->foreign('locaId')->references('id')->on('locataires');

            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs');

            // Champs Anglais
            $table->text('contenu_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
