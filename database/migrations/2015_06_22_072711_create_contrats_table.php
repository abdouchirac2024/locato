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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('refContrat_fr');  // Référence du contrat en français
            $table->binary('fichierJoint');  // Fichier joint (contrat en binaire)
            $table->date('dateSignature');  // Date de signature
            $table->timestamps();

            // Liens vers les autres tables
            $table->unsignedBigInteger('typContId');  // Type de contrat
            $table->foreign('typContId')->references('id')->on('type_contrats');
            $table->unsignedBigInteger('logId');  // Logement
            $table->foreign('logId')->references('id')->on('logements');
            $table->unsignedBigInteger('bailId');  // Bailleur
            $table->foreign('bailId')->references('id')->on('bailleurs');

            // Champs Anglais
            $table->string('refContrat_en')->nullable();  // Référence du contrat en anglais
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
