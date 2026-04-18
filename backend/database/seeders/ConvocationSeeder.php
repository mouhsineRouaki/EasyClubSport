<?php

namespace Database\Seeders;

use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\MembreEquipe;
use Illuminate\Database\Seeder;

class ConvocationSeeder extends Seeder
{
    public function run(): void
    {
        $equipes = Equipe::all();

        foreach ($equipes as $equipe) {
            $joueurIds = MembreEquipe::where('equipe_id', $equipe->id)
                ->where('role_equipe', 'joueur')
                ->pluck('utilisateur_id');

            if ($joueurIds->isEmpty()) continue;

            $evenements = Evenement::where('equipe_id', $equipe->id)->get();

            foreach ($evenements as $evenement) {
                $convoquesIds = $joueurIds->random(min($joueurIds->count(), rand(8, min($joueurIds->count(), 14))));

                foreach ($convoquesIds as $joueurId) {
                    $statut = $this->statut($evenement->statut);

                    Convocation::firstOrCreate(
                        ['evenement_id' => $evenement->id, 'utilisateur_id' => $joueurId],
                        [
                            'statut'            => $statut,
                            'date_convocation'  => $evenement->date_debut->subDays(rand(2, 7)),
                            'date_confirmation' => in_array($statut, ['confirme', 'refuse'])
                                ? $evenement->date_debut->subDays(rand(0, 2))
                                : null,
                        ]
                    );
                }
            }
        }
    }

    private function statut(string $statutEvenement): string
    {
        if ($statutEvenement === 'termine') {
            return fake()->randomElement(['confirme', 'confirme', 'confirme', 'refuse', 'en_attente']);
        }

        return fake()->randomElement(['convoque', 'convoque', 'confirme', 'refuse', 'en_attente']);
    }
}
