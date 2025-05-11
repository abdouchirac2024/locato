<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locataires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('preference')->nullable();
            $table->integer('nrbvist')->default(0);
            // $table->enum('status_fr', ['actif', 'inactif'])->default('inactif');
            // $table->enum('status_en', ['active', 'inactive'])->default('inactive');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locataires');
    }
};
