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
        Schema::table('visites', function (Blueprint $table) {
            $table->enum('statut_en', [
                'SCHEDULED', 'CANCELLED', 'COMPLETED', 'POSTPONED', // Existants
                'REQUESTED', // Pour DEMANDEE
                'CANCELLED_BY_TENANT', // Pour ANNULEE_LOCATAIRE
                'CANCELLED_BY_LANDLORD', // Pour ANNULEE_BAILLEUR
                'POSTPONED_BY_LANDLORD', // Pour REPORTEE_BAILLEUR
                'COUNTER_PROPOSED' // Pour CONTRE_PROPOSITION
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            // Revenir aux valeurs originales (attention si des nouveaux statuts sont déjà en BDD)
            $table->enum('statut_en', ['SCHEDULED', 'CANCELLED', 'COMPLETED', 'POSTPONED'])->change();
        });
    }
};
