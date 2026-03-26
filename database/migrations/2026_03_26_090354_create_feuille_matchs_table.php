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
        Schema::create('feuille_matchs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->unique()->constrained('evenements')->cascadeOnDelete();
            $table->string('formation')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('est_validee')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feuille_matchs');
    }
};
