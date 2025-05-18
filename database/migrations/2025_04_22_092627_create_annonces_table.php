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
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->string('titre_fr');
            $table->string('titre_en')->nullable();
            $table->text('contenu_fr');
            $table->text('contenu_en')->nullable();
            $table->date('date_publication');
            $table->date('date_expiration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('create_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->unsignedBigInteger('logId');
            $table->foreign('logId')->references('id')->on('logements');
            $table->unsignedBigInteger('bailId');
            $table->foreign('bailId')->references('id')->on('bailleurs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
