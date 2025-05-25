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
            $table->text('commentaire_locataire')->nullable()->after('statut_en');
            $table->text('commentaire_bailleur')->nullable()->after('commentaire_locataire');
            $table->boolean('confirmation_bailleur')->nullable()->after('commentaire_bailleur');
            $table->date('date_proposee_bailleur')->nullable()->after('confirmation_bailleur');
            $table->time('heure_proposee_bailleur')->nullable()->after('date_proposee_bailleur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            $table->dropColumn(['commentaire_locataire', 'commentaire_bailleur', 'confirmation_bailleur', 'date_proposee_bailleur', 'heure_proposee_bailleur']);
        });
    }
};
