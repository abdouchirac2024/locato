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
            $table->string('libelle');
            $table->text('description_fr')->nullable();
            $table->string('option');
            $table->float('pourcentage');
            $table->timestamps();

            // Champs Anglais
            $table->text('description_en')->nullable();
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
