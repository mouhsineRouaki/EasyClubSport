<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PresidentSeeder::class,
            ClubSeeder::class,
            CoachSeeder::class,
            EquipeSeeder::class,
            JoueurSeeder::class,
            EvenementSeeder::class,
            ConvocationSeeder::class,
            CanalSeeder::class,
            MessageSeeder::class,
            AnnonceSeeder::class,
            NotificationSeeder::class,
            CotisationSeeder::class,
        ]);
    }
}
