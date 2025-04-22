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
        Schema::create('bailleurs', function (Blueprint $table) {
            $table->id();
            $table->boolean('verif')->default(false); // verification du compte du bailleur
            $table->string('numFiscal')->nullable(); // numero fiscale
            $table->text('description_fr')->nullable();
            $table->integer('nbrLog');  // nombre de logement
              // Champs Anglais
              $table->text('description_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailleurs');
    }
};
