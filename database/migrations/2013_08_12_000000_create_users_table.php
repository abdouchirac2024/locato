<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('prenom')->nullable();
            $table->string('matricule', 7)->unique();
            $table->string('telephone')->unique();
            $table->string('photoProfile')->nullable();
            $table->string('cni')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->enum('role', ['ADMIN', 'Locataire', 'Bailleur']);
            $table->string('verification_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->unsignedBigInteger('quartier_id')->nullable();
            $table->foreign('quartier_id')->references('id')->on('quartiers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
