<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\SeederImageStorage;
use Illuminate\Database\Seeder;

class PresidentSeeder extends Seeder
{
    public function run(): void
    {
        $images = new SeederImageStorage();

        $president = User::factory()->president()->create([
            'name'   => 'Ahmed Benali',
            'nom'    => 'Benali',
            'prenom' => 'Ahmed',
            'email'  => 'president@easyclubsport.com',
        ]);

        $president->update([
            'photo' => $images->userPhoto($president->name, 'president'),
        ]);

        $secondPresident = User::factory()->president()->create([
            'name'   => 'Karim Mansouri',
            'nom'    => 'Mansouri',
            'prenom' => 'Karim',
            'email'  => 'president2@easyclubsport.com',
        ]);

        $secondPresident->update([
            'photo' => $images->userPhoto($secondPresident->name, 'president'),
        ]);

        User::factory()
            ->president()
            ->count(3)
            ->create()
            ->each(function (User $user) use ($images): void {
                $user->update([
                    'photo' => $images->userPhoto($user->name, 'president'),
                ]);
            });
    }
}
