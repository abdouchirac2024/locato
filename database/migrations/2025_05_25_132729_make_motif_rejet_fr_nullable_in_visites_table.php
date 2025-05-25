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
            $table->text('motifRejet_fr')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            // Note: Remettre une colonne non nullable peut nécessiter de s'assurer qu'il n'y a pas de NULL
            // ou de définir une valeur par défaut avant de revenir en arrière.
             $table->text('motifRejet_fr')->nullable(false)->change();
        });
    }
};
