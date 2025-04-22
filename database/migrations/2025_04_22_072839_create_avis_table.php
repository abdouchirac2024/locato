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
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->dateTime('dateEnv');
            $table->text('coment_fr');
            $table->integer('note');
            $table->boolean('visible')->default(false);
            $table->timestamps();
              // migration
            $table->unsignedBigInteger('locaId');// locataire id
            $table->foreign('locaId')->references('id')->on('locataire');
            $table->unsignedBigInteger('logId'); // logement id
            $table->foreign('logId')->references('id')->on('logements');

             // Champs Anglais
             $table->text('coment_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
