<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnonceFactory extends Factory
{
    public function definition(): array
    {
        $titres = [
            'Convocation pour le prochain match',
            'Changement d\'horaire d\'entraînement',
            'Inscription au tournoi régional',
            'Réunion de fin de saison',
            'Renouvellement des licences',
            'Stage de formation technique',
            'Résultats du dernier match',
            'Planning des vacances sportives',
        ];

        return [
            'club_id'    => Club::factory(),
            'auteur_id'  => User::factory()->president(),
            'titre'      => fake()->randomElement($titres),
            'contenu'    => fake('fr_FR')->paragraphs(2, true),
            'image'      => null,
            'est_active' => fake()->boolean(80),
        ];
    }
}
