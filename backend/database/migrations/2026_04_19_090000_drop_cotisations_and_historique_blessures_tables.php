<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('cotisations');
        Schema::dropIfExists('historique_blessures');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement')->nullable();
            $table->string('statut_paiement')->default('en_attente');
            $table->string('saison')->nullable();
            $table->timestamps();
        });

        Schema::create('historique_blessures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('type_blessure');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
