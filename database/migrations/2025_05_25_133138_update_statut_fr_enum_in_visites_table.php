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
            $table->enum('statut_fr', ['PROGRAMMEE', 'ANNULEE', 'TERMINEE', 'REPORTEE', 'DEMANDEE', 'ANNULEE_LOCATAIRE', 'ANNULEE_BAILLEUR', 'REPORTEE_BAILLEUR', 'EFFECTUEE', 'CONTRE_PROPOSITION'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            $table->enum('statut_fr', ['PROGRAMMEE', 'ANNULEE', 'TERMINEE', 'REPORTEE'])->change();
        });
    }
};
