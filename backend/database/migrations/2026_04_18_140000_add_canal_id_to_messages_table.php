<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('canal_id')
                ->nullable()
                ->after('id')
                ->constrained('canaux')
                ->nullOnDelete();
        });

        $messages = DB::table('messages')
            ->select('id', 'equipe_id')
            ->whereNull('canal_id')
            ->whereNotNull('equipe_id')
            ->get();

        foreach ($messages as $message) {
            $canalId = DB::table('canaux')
                ->where('equipe_id', $message->equipe_id)
                ->orderBy('id')
                ->value('id');

            if ($canalId) {
                DB::table('messages')
                    ->where('id', $message->id)
                    ->update(['canal_id' => $canalId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('canal_id');
        });
    }
};
