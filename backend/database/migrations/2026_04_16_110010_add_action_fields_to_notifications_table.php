<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('evenement_id')
                ->nullable()
                ->after('utilisateur_id')
                ->constrained('evenements')
                ->nullOnDelete();
            $table->string('action')->nullable()->after('type_notification');
            $table->string('statut_action')->nullable()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evenement_id');
            $table->dropColumn(['action', 'statut_action']);
        });
    }
};
