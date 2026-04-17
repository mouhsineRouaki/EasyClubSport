<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->string('statut_invitation_adversaire')->default('sans_invitation')->after('statut');
            $table->foreignId('invitation_adversaire_repondue_par_id')
                ->nullable()
                ->after('statut_invitation_adversaire')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('invitation_adversaire_repondue_at')
                ->nullable()
                ->after('invitation_adversaire_repondue_par_id');
        });
    }

    public function down(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invitation_adversaire_repondue_par_id');
            $table->dropColumn([
                'statut_invitation_adversaire',
                'invitation_adversaire_repondue_at',
            ]);
        });
    }
};
