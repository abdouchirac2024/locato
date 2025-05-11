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
            $table->dateTime('dateEnv'); // Date d'envoi
            $table->dateTime('dateLect')->nullable(); // Date de lecture
            $table->boolean('lecture')->nullable();
            $table->timestamps();

            // Clés étrangères avec suppression en cascade
            $table->unsignedBigInteger('locaId');
            $table->foreign('locaId')->references('id')->on('locataires')->onDelete('cascade');

            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs')->onDelete('cascade');

            // Champs Anglais
            $table->text('contenu_en')->nullable();

            // Index pour les colonnes de recherche
            $table->index(['locaId', 'bailId']);
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
