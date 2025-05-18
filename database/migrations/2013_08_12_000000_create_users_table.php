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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('prenom')->nullable();
            $table->string('matricule', 7)->unique();
            $table->string('email')->unique()->nullable();
            $table->string('telephone')->unique();
            $table->string('photoProfile')->nullable();
            $table->string('cni')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('role', ['ADMIN', 'Locataire', 'Bailleur']);
            $table->string('verification_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->unsignedBigInteger('quartier_id')->nullable();
            $table->foreign('quartier_id')
                  ->references('id')->on('quartiers')
                  ->onDelete('set null'); // Si le quartier est supprimé, mettre à NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
