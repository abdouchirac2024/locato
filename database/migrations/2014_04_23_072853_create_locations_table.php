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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->float('montant');
            $table->date('dateDeb');
            $table->date('dateFin');
            $table->string('caution');
            $table->enum('statut_fr', ['En cours', 'Terminé', 'Annulé'])->default('En cours');
            $table->enum('statut_en', ['In progress', 'Completed', 'Cancelled'])->default('In progress');

            // Foreign keys
            $table->unsignedBigInteger('locaId');
            $table->unsignedBigInteger('bailId');
            $table->unsignedBigInteger('logId');

            $table->timestamps();

            $table->foreign('locaId')->references('id')->on('locataires')->onDelete('cascade');
            $table->foreign('bailId')->references('id')->on('bailleurs')->onDelete('cascade');
            $table->foreign('logId')->references('id')->on('logements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
