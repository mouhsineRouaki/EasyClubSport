<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_joueur')->nullable()->after('photo');
            $table->string('poste_principal')->nullable()->after('numero_joueur');
            $table->string('poste_secondaire')->nullable()->after('poste_principal');
            $table->enum('pied_fort', ['droit', 'gauche', 'ambidextre'])->nullable()->after('poste_secondaire');
            $table->unsignedTinyInteger('note_globale')->nullable()->after('pied_fort');
            $table->unsignedTinyInteger('attaque')->nullable()->after('note_globale');
            $table->unsignedTinyInteger('defense')->nullable()->after('attaque');
            $table->unsignedTinyInteger('vitesse')->nullable()->after('defense');
            $table->unsignedTinyInteger('passe')->nullable()->after('vitesse');
            $table->unsignedTinyInteger('dribble')->nullable()->after('passe');
            $table->unsignedTinyInteger('physique')->nullable()->after('dribble');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'numero_joueur',
                'poste_principal',
                'poste_secondaire',
                'pied_fort',
                'note_globale',
                'attaque',
                'defense',
                'vitesse',
                'passe',
                'dribble',
                'physique',
            ]);
        });
    }
};
