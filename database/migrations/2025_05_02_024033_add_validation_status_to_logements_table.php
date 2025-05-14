<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logements', function (Blueprint $table) {
            // Ajoute le statut après la colonne 'dispo_en' (ou une autre colonne pertinente)
            $table->enum('validation_status', ['en_attente', 'approuve', 'rejete'])
                  ->default('en_attente') // Nouveau logement est en attente par défaut
                  ->after('dispo_en');

            // Ajoute une colonne pour les notes de l'admin (ex: motif du rejet)
            $table->text('admin_notes')->nullable()->after('validation_status');
        });
    }

    public function down(): void
    {
        Schema::table('logements', function (Blueprint $table) {
            $table->dropColumn(['validation_status', 'admin_notes']);
        });
    }
};
