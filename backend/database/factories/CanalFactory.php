<?php

namespace Database\Factories;

use App\Models\Equipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class CanalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipe_id'   => Equipe::factory(),
            'nom'         => fake()->randomElement(['Général', 'Tactique', 'Annonces', 'Résultats', 'Planning']),
            'type_canal'  => 'equipe',
            'description' => fake('fr_FR')->sentence(),
        ];
    }

    public function prive(): static
    {
        return $this->state(fn () => [
            'equipe_id'  => null,
            'type_canal' => 'prive',
        ]);
    }
}
