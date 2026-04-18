<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->coach()->create([
            'name'   => 'Youssef Alami',
            'nom'    => 'Alami',
            'prenom' => 'Youssef',
            'email'  => 'coach@easyclubsport.com',
        ]);

        User::factory()->coach()->create([
            'name'   => 'Rachid Ouali',
            'nom'    => 'Ouali',
            'prenom' => 'Rachid',
            'email'  => 'coach2@easyclubsport.com',
        ]);

        User::factory()->coach()->count(8)->create();
    }
}
