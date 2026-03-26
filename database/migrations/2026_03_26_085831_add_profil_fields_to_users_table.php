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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom')->nullable()->after('id');
            $table->string('prenom')->nullable()->after('nom');
            $table->string('telephone')->nullable()->after('email');
            $table->string('adresse')->nullable()->after('telephone');
            $table->string('photo')->nullable()->after('adresse');
            $table->enum('role', ['admin', 'president', 'coach', 'joueur'])->default('joueur')->after('photo');
            $table->enum('statut', ['actif', 'blesse', 'suspendu', 'inactif'])->default('actif')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nom',
                'prenom',
                'telephone',
                'adresse',
                'photo',
                'role',
                'statut',
            ]);
        });
    }
};
