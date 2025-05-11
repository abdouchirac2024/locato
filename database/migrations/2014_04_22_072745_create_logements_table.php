<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable(); // Ajout basé sur modèle
            $table->string('libelle')->nullable(); // Champ FR principal
             $table->string('libelle_en')->nullable(); // Décommentez si vous voulez la traduction du libellé
            $table->decimal('latitude', 10, 8)->nullable(); // Ajout basé sur modèle
            $table->decimal('longitude', 11, 8)->nullable(); // Ajout basé sur modèle
            $table->integer('nbrpieces');
            $table->integer('superficie')->nullable(); // Ajout basé sur modèle
            $table->integer('nbr_salles_bain')->nullable(); // Ajout basé sur modèle
            $table->integer('nbr_chambres')->nullable(); // Ajout basé sur modèle
            $table->boolean('climatisation')->default(false); // Ajout basé sur modèle
            $table->boolean('meuble')->nullable()->default(false); // Ajout basé sur modèle
            $table->string('adresse')->nullable(); // Ajout basé sur modèle
            $table->float('prix');
            $table->integer('nbrMois'); // Renommé depuis la migration initiale ? Ou était déjà là.
            $table->text('descrip_fr')->nullable();
            $table->text('descrip_en')->nullable();
            $table->boolean('contPrep')->nullable()->default(false);
            $table->boolean('forage')->nullable()->default(false);
            $table->boolean('parking')->nullable()->default(false);
            $table->boolean('gardien')->nullable()->default(false);
            $table->enum('dispo_fr', ['LIBRE', 'OCCUPE'])->default('LIBRE');
            $table->enum('dispo_en', ['FREE', 'RENTED'])->nullable();
            $table->timestamps();

            // Clés étrangères
            $table->unsignedBigInteger('typLogId');
            $table->foreign('typLogId')->references('id')->on('type_logements')->onDelete('restrict');

            $table->unsignedBigInteger('quartierId');
            $table->foreign('quartierId')->references('id')->on('quartiers')->onDelete('restrict');

            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logements');
    }
};
