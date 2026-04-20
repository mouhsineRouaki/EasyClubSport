<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feuille_matchs', function (Blueprint $table) {
            $table->unsignedInteger('score_equipe')->nullable()->after('est_validee');
            $table->unsignedInteger('score_adversaire')->nullable()->after('score_equipe');
            $table->text('resume_match')->nullable()->after('score_adversaire');
        });
    }

    public function down(): void
    {
        Schema::table('feuille_matchs', function (Blueprint $table) {
            $table->dropColumn([
                'score_equipe',
                'score_adversaire',
                'resume_match',
            ]);
        });
    }
};
