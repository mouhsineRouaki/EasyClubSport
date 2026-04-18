<?php

namespace Database\Seeders;

use App\Models\Canal;
use App\Models\CanalUtilisateur;
use App\Models\Equipe;
use App\Models\MembreEquipe;
use Illuminate\Database\Seeder;

class CanalSeeder extends Seeder
{
    public function run(): void
    {
        $equipes = Equipe::all();

        foreach ($equipes as $equipe) {
            $membres = MembreEquipe::where('equipe_id', $equipe->id)->pluck('utilisateur_id');

            $canaux = [
                ['Général',     'Canal principal de l\'équipe'],
                ['Tactique',    'Discussions tactiques et stratégies'],
                ['Annonces',    'Annonces officielles du coach'],
            ];

            foreach ($canaux as [$nom, $description]) {
                $canal = Canal::create([
                    'equipe_id'   => $equipe->id,
                    'nom'         => $nom,
                    'type_canal'  => 'equipe',
                    'description' => $description,
                ]);

                foreach ($membres as $membreId) {
                    CanalUtilisateur::firstOrCreate(
                        ['canal_id' => $canal->id, 'utilisateur_id' => $membreId]
                    );
                }
            }
        }
    }
}
