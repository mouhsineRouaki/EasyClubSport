<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\SeederImageStorage;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        $images = new SeederImageStorage();

        $coach = User::factory()->coach()->create([
            'name'   => 'Youssef Alami',
            'nom'    => 'Alami',
            'prenom' => 'Youssef',
            'email'  => 'coach@easyclubsport.com',
        ]);

        $coach->update([
            'photo' => $images->userPhoto($coach->name, 'coach'),
        ]);

        $secondCoach = User::factory()->coach()->create([
            'name'   => 'Rachid Ouali',
            'nom'    => 'Ouali',
            'prenom' => 'Rachid',
            'email'  => 'coach2@easyclubsport.com',
        ]);

        $secondCoach->update([
            'photo' => $images->userPhoto($secondCoach->name, 'coach'),
        ]);

        User::factory()
            ->coach()
            ->count(8)
            ->create()
            ->each(function (User $user) use ($images): void {
                $user->update([
                    'photo' => $images->userPhoto($user->name, 'coach'),
                ]);
            });
    }
}
