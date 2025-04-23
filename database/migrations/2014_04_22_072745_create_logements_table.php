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
            $table->integer('nbrMois');
            $table->float('prix');
            $table->integer('nbrpieces');
            $table->text('descrip')->nullable();//description
            $table->boolean('contPrep')->nullable();//conteur prepayé
            $table->boolean('forage')->nullable();
            $table->boolean('parking')->nullable();
            $table->boolean('gardien')->nullable();
            $table->enum('dispo', ['LIBRE', 'OCCUPE'])->default('LIBRE');
            $table->timestamps();
             // migration
            $table->unsignedBigInteger('typLogId');
            $table->foreign('typLogId')->references('id')->on('type_logements');
            $table->unsignedBigInteger('quartierId');
            $table->foreign('quartierId')->references('id')->on('quartiers');
            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs'); // id bailleur

              // Champs Anglais
             $table->text('descrip_en')->nullable();
             $table->enum('dispo_en', ['FREE', 'RENTED'])->default('FREE');
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
