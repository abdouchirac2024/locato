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
            $table->text('msg_fr'); // Message in French
            $table->enum('result', ['ACCEPTE', 'REFUSE'])->nullable(); // Result in French
            $table->enum('result_en', ['ACCEPTE', 'REFUSE'])->nullable(); // Result in English
            $table->date('dateEnv'); // Date of sending
            $table->timestamps();

            // Foreign keys
            $table->unsignedBigInteger('locaId'); // locataire id
            $table->foreign('locaId')->references('id')->on('locataires')->onDelete('cascade');

            $table->unsignedBigInteger('bailId'); // bailleur id
            $table->foreign('bailId')->references('id')->on('bailleurs')->onDelete('cascade');

            // English fields
            $table->text('msg_en')->nullable(); // Message in English
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
