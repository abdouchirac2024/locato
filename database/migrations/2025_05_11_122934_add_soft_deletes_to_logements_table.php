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
        Schema::table('logements', function (Blueprint $table) {
            // La méthode softDeletes() ajoute une colonne 'deleted_at' nullable
            // et la place idéalement après 'updated_at' si cette colonne existe.
            // Si 'updated_at' n'existe pas, elle sera ajoutée à la fin.
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logements', function (Blueprint $table) {
            // Vérifier si la colonne existe avant de tenter de la supprimer
            if (Schema::hasColumn('logements', 'deleted_at')) {
                $table->dropSoftDeletes(); // Supprime la colonne 'deleted_at'
            }
        });
    }
};
