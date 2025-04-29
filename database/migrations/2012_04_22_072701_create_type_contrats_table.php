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
        Schema::create('type_contrats', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');  // libelle en français
            $table->string('libelle_en');  // libelle en anglais
            $table->text('description_fr')->nullable();  // description en français
            $table->string('option');  // option (type de contrat)
            $table->float('pourcentage');  // pourcentage associé au contrat
            $table->timestamps();

            // Champs Anglais
            $table->text('description_en')->nullable();  // description en anglais
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_contrats');
    }
};
