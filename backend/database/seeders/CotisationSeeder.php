<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Cotisation;
use App\Models\Equipe;
use App\Models\MembreEquipe;
use Illuminate\Database\Seeder;

class CotisationSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = Club::all();

        foreach ($clubs as $club) {
            $equipeIds  = Equipe::where('club_id', $club->id)->pluck('id');
            $joueurIds  = MembreEquipe::whereIn('equipe_id', $equipeIds)
                ->where('role_equipe', 'joueur')
                ->pluck('utilisateur_id')
                ->unique();

            foreach ($joueurIds as $joueurId) {
                $statut = fake()->randomElement(['paye', 'paye', 'paye', 'en_attente', 'en_retard']);
                Cotisation::create([
                    'club_id'         => $club->id,
                    'utilisateur_id'  => $joueurId,
                    'montant'         => fake()->randomElement([200, 300, 350, 400, 500]),
                    'date_paiement'   => $statut === 'paye' ? now()->subDays(rand(1, 90))->toDateString() : null,
                    'statut_paiement' => $statut,
                    'saison'          => '2024-2025',
                ]);
            }
        }
    }
}
