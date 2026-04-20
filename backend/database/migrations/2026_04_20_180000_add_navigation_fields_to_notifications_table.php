<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('canal_id')
                ->nullable()
                ->after('evenement_id')
                ->constrained('canaux')
                ->nullOnDelete();

            $table->foreignId('convocation_id')
                ->nullable()
                ->after('canal_id')
                ->constrained('convocations')
                ->nullOnDelete();

            $table->string('module_cible')->nullable()->after('statut_action');
            $table->unsignedBigInteger('cible_id')->nullable()->after('module_cible');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('canal_id');
            $table->dropConstrainedForeignId('convocation_id');
            $table->dropColumn(['module_cible', 'cible_id']);
        });
    }
};
