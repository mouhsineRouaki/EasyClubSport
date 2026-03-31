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
        Schema::create('statistique_matchs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feuille_match_id')->constrained('feuille_matchs')->cascadeOnDelete();
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('score_equipe')->default(0);
            $table->unsignedInteger('score_adversaire')->default(0);
            $table->unsignedInteger('buts')->default(0);
            $table->unsignedInteger('passes_decisives')->default(0);
            $table->unsignedInteger('cartons_jaunes')->default(0);
            $table->unsignedInteger('cartons_rouges')->default(0);
            $table->unsignedInteger('minutes_jouees')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistique_matchs');
    }
};
