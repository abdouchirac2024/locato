<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            // S'assurer que locaId existe et est bien lié
            if (!Schema::hasColumn('visites', 'locaId')) {
                $table->unsignedBigInteger('locaId')->after('logId'); // Ou après payId si gardé
                $table->foreign('locaId')->references('id')->on('locataires')->onDelete('cascade');
            } else {
                // Si locaId existe mais n'a pas de contrainte ou mauvaise, on la recrée
                // $table->dropForeign(['locaId']); // S'assurer de dropper l'ancienne si elle existe mal
                // $table->foreign('locaId')->references('id')->on('locataires')->onDelete('cascade')->change();
            }

            // Supprimer payId si on ne l'utilise plus directement pour la demande de visite
            if (Schema::hasColumn('visites', 'payId')) {
                 // $table->dropForeign(['payId']); // Si une FK existait
                 // $table->dropColumn('payId');
            }

            // Adapter les statuts et commentaires
            // Renommer 'confirmation' en 'confirmation_bailleur'
            if (Schema::hasColumn('visites', 'confirmation')) {
                $table->renameColumn('confirmation', 'confirmation_bailleur');
            } elseif(!Schema::hasColumn('visites', 'confirmation_bailleur')) {
                 $table->boolean('confirmation_bailleur')->nullable()->default(false)->after('heureVisite');
            }

            // Renommer 'commentaire' en 'commentaire_locataire'
            if (Schema::hasColumn('visites', 'commentaire')) {
                $table->renameColumn('commentaire', 'commentaire_locataire');
            } elseif(!Schema::hasColumn('visites', 'commentaire_locataire')) {
                 $table->text('commentaire_locataire')->nullable()->after('confirmation_bailleur');
            }

            // Ajouter commentaire_bailleur
            if (!Schema::hasColumn('visites', 'commentaire_bailleur')) {
                $table->text('commentaire_bailleur')->nullable()->after('commentaire_locataire');
            }

             // Assurer que motifRejet_fr est nullable comme le modèle le suggère pour traduction
             $table->text('motifRejet_fr')->nullable()->change();


            // Ajuster les enums de statut pour inclure plus d'états
            $table->enum('statut_fr', [
                'DEMANDEE', // Nouvelle valeur par défaut
                'PROGRAMMEE', // Confirmée par bailleur
                'ANNULEE_LOCATAIRE',
                'ANNULEE_BAILLEUR',
                'REPORTEE_BAILLEUR', // Bailleur propose autre date/heure
                'EFFECTUEE',
                'CONTRE_PROPOSITION' // Si le bailleur propose autre chose
                ])->default('DEMANDEE')->change();

            $table->enum('statut_en', [
                'REQUESTED',
                'SCHEDULED',
                'CANCELLED_TENANT',
                'CANCELLED_LANDLORD',
                'POSTPONED_LANDLORD',
                'COMPLETED',
                'COUNTER_PROPOSAL'
                ])->default('REQUESTED')->change();

            // Champs pour la contre-proposition du bailleur
            if (!Schema::hasColumn('visites', 'date_proposee_bailleur')) {
                $table->date('date_proposee_bailleur')->nullable()->after('motifRejet_en');
            }
            if (!Schema::hasColumn('visites', 'heure_proposee_bailleur')) {
                $table->time('heure_proposee_bailleur')->nullable()->after('date_proposee_bailleur');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            // Revenir en arrière est complexe, se concentrer sur le up pour l'instant
            // Il faudrait stocker l'état précédent pour un rollback parfait
            // $table->dropColumn(['date_proposee_bailleur', 'heure_proposee_bailleur', 'commentaire_bailleur']);
            // if(Schema::hasColumn('visites', 'commentaire_locataire')) $table->renameColumn('commentaire_locataire', 'commentaire');
            // if(Schema::hasColumn('visites', 'confirmation_bailleur')) $table->renameColumn('confirmation_bailleur', 'confirmation');
            // Modifier les enums pour revenir à l'état précédent...
        });
    }
};