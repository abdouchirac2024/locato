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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('msg');
            $table->enum('result', ['ACCEPTE', 'REFUSE'])->nullable();
            $table->date('dateEnv');
            $table->timestamps();
            // migration
            $table->unsignedBigInteger('locaId'); // locataire id
            $table->foreign('locaId')->references('id')->on('locataires');
            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs'); // bailleur id

             // Champs Anglais
             $table->text('msg_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
