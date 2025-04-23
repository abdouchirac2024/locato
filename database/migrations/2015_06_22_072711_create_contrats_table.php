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
            $table->string('refContrat_fr');
            $table->binary('fichierJoint');
            $table->date('dateSignature');
            $table->timestamps();
               // migration
            $table->unsignedBigInteger('typContId');
            $table->foreign('typContId')->references('id')->on('type_contrats');
            $table->unsignedBigInteger('logId');
            $table->foreign('logId')->references('id')->on('logements');
            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs');

             // Champs Anglais
             $table->string('refContrat_en')->nullable();
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
