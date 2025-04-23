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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->float('montant');
            $table->date('dateDeb');
            $table->date('dateFin');
            $table->string('caution');
            $table->enum('statut_fr', ['En cours', 'Terminé', 'Annulé'])->default('En cours');
            $table->timestamps();
             // migration
            $table->unsignedBigInteger('locaId');
            $table->foreign('locaId')->references('id')->on('locataires');
            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs');
            $table->unsignedBigInteger('logId');
            $table->foreign('logId')->references('id')->on('logements');

             // Champs Anglais
            $table->enum('statut_en', ['In progress', 'Completed', 'Cancelled'])->default('In progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
