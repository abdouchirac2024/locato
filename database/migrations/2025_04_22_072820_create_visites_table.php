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
        Schema::create('visites', function (Blueprint $table) {
            $table->id();
            $table->date('dateVisite');
            $table->time('heureVisite');
            $table->enum('statut', ['ACCEPTE', 'REFUSE'])->nullable();
            $table->boolean('confirmation')->nullable();
            $table->text('motifRejet_fr');
            $table->timestamps();
              // migration
            $table->unsignedBigInteger('payId');
            $table->foreign('payId')->references('id')->on('payements');
            $table->unsignedBigInteger('logId');
            $table->foreign('logId')->references('id')->on('logements');

             // Champs Anglais
             $table->text('motifRejet_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visites');
    }
};
