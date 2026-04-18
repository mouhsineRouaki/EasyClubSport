<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $presidents = User::where('role', 'president')->get();

        $clubsData = [
            [
                'nom'         => 'Club Sportif Atlas Casablanca',
                'ville'       => 'Casablanca',
                'adresse'     => '12 Boulevard Mohammed V',
                'telephone'   => '+212 522 345 678',
                'email'       => 'contact@atlas-casa.ma',
                'description' => 'Club fondé en 1985, spécialisé en football et basketball. Nous formons les talents de demain.',
            ],
            [
                'nom'         => 'Royal Club Sportif Rabat',
                'ville'       => 'Rabat',
                'adresse'     => '5 Rue des Orangers, Agdal',
                'telephone'   => '+212 537 123 456',
                'email'       => 'info@rcs-rabat.ma',
                'description' => 'Club multisports avec plus de 500 membres actifs dans différentes disciplines.',
            ],
            [
                'nom'         => 'Association Sportive Marrakech',
                'ville'       => 'Marrakech',
                'adresse'     => '8 Avenue Mohamed VI',
                'telephone'   => '+212 524 789 012',
                'email'       => 'asm@sport-marrakech.ma',
                'description' => 'Depuis 1990, nous cultivons l\'excellence sportive dans la ville ocre.',
            ],
        ];

        foreach ($clubsData as $i => $data) {
            Club::create([
                ...$data,
                'president_id' => $presidents->get($i)?->id ?? $presidents->first()->id,
                'pays'         => 'Maroc',
                'logo'         => null,
            ]);
        }

        $remainingPresidents = $presidents->slice(3);
        foreach ($remainingPresidents as $president) {
            Club::factory()->create(['president_id' => $president->id]);
        }
    }
}
