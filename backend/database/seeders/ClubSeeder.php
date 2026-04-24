<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Database\Seeders\Support\SeederImageStorage;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $images = new SeederImageStorage();
        $presidents = User::where('role', 'president')->get();

        $clubsData = [
            [
                'nom' => 'Club Sportif Atlas Casablanca',
                'ville' => 'Casablanca',
                'adresse' => '12 Boulevard Mohammed V',
                'telephone' => '+212 522 345 678',
                'email' => 'contact@atlas-casa.ma',
                'description' => 'Club fonde en 1985, specialise en football et basketball. Nous formons les talents de demain.',
            ],
            [
                'nom' => 'Royal Club Sportif Rabat',
                'ville' => 'Rabat',
                'adresse' => '5 Rue des Orangers, Agdal',
                'telephone' => '+212 537 123 456',
                'email' => 'info@rcs-rabat.ma',
                'description' => 'Club multisports avec plus de 500 membres actifs dans differentes disciplines.',
            ],
            [
                'nom' => 'Association Sportive Marrakech',
                'ville' => 'Marrakech',
                'adresse' => '8 Avenue Mohamed VI',
                'telephone' => '+212 524 789 012',
                'email' => 'asm@sport-marrakech.ma',
                'description' => 'Depuis 1990, nous cultivons l excellence sportive dans la ville ocre.',
            ],
        ];

        foreach ($clubsData as $index => $data) {
            Club::create([
                ...$data,
                'president_id' => $presidents->get($index)?->id ?? $presidents->first()->id,
                'pays' => 'Maroc',
                'logo' => $images->clubLogo($data['nom']),
            ]);
        }

        $remainingPresidents = $presidents->slice(3);

        foreach ($remainingPresidents as $president) {
            $club = Club::factory()->create([
                'president_id' => $president->id,
            ]);

            $club->update([
                'logo' => $images->clubLogo($club->nom),
            ]);
        }
    }
}
