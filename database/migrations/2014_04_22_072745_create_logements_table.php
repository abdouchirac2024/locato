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
        Schema::create('logements', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->integer('nbrMois'); // Nombre de mois d'avance exigés
            $table->float('prix'); // Prix du loyer mensuel
            $table->integer('nbrpieces'); // Nombre de pièces
            $table->text('descrip_fr')->nullable(); // Description en français (source)
            $table->boolean('contPrep')->nullable()->default(false); // compteur prépayé ?
            $table->boolean('forage')->nullable()->default(false); // forage ?
            $table->boolean('parking')->nullable()->default(false); // parking ?
            $table->boolean('gardien')->nullable()->default(false); // gardien ?
            $table->enum('dispo_fr', ['LIBRE', 'OCCUPE'])->default('LIBRE'); // Disponibilité en français (source)
            $table->timestamps();

            // Clés étrangères
            $table->unsignedBigInteger('typLogId'); // ID du type de logement
            $table->foreign('typLogId')->references('id')->on('type_logements')->onDelete('restrict'); // Assurez-vous que type_logements existe

            $table->unsignedBigInteger('quartierId'); // ID du quartier
            $table->foreign('quartierId')->references('id')->on('quartiers')->onDelete('restrict'); // Assurez-vous que quartiers existe

            $table->unsignedBigInteger('bailId'); // ID du bailleur propriétaire
            $table->foreign('bailId')->references('id')->on('bailleurs')->onDelete('cascade'); // Assurez-vous que bailleurs existe

            // Champs pour la traduction automatique
            $table->text('descrip_en')->nullable(); // Description traduite en anglais
            $table->enum('dispo_en', ['FREE', 'RENTED'])->nullable(); // Disponibilité traduite en anglais
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logements');
    }
};
