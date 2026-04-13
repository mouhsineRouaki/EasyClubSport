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
        Schema::create('canaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipe_id')->nullable()->constrained('equipes')->nullOnDelete();
            $table->string('nom');
            $table->enum('type_canal', ['equipe', 'prive', 'club'])->default('equipe');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canaux');
    }
};
