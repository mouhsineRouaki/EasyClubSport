<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipeFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['U10', 'U12', 'U14', 'U16', 'U18', 'Senior', 'Vétérans', 'Féminin'];
        $noms       = ['Les Lions', 'Les Aigles', 'Les Étoiles', 'Les Champions', 'Les Guerriers', 'Les Faucons', 'Les Tigres', 'Les Dragons'];

        return [
            'club_id'         => Club::factory(),
            'coach_id'        => User::factory()->coach(),
            'nom'             => fake()->randomElement($noms) . ' ' . fake()->randomElement($categories),
            'categorie'       => fake()->randomElement($categories),
            'logo'            => null,
            'code_invitation' => strtoupper(Str::random(8)),
            'statut'          => 'active',
            'description'     => fake('fr_FR')->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['statut' => 'inactive']);
    }
}
