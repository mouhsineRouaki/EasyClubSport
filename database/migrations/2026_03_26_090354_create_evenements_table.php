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
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipe_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('createur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titre');
            $table->enum('type', ['match', 'entrainement', 'reunion'])->default('entrainement');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->string('lieu')->nullable();
            $table->string('adversaire')->nullable();
            $table->text('description')->nullable();
            $table->enum('statut', ['planifie', 'termine', 'annule'])->default('planifie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
