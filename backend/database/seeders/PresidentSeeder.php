<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PresidentSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->president()->create([
            'name'   => 'Ahmed Benali',
            'nom'    => 'Benali',
            'prenom' => 'Ahmed',
            'email'  => 'president@easyclubsport.com',
        ]);

        User::factory()->president()->create([
            'name'   => 'Karim Mansouri',
            'nom'    => 'Mansouri',
            'prenom' => 'Karim',
            'email'  => 'president2@easyclubsport.com',
        ]);

        User::factory()->president()->count(3)->create();
    }
}
