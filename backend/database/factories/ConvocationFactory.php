<?php

namespace Database\Factories;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConvocationFactory extends Factory
{
    public function definition(): array
    {
        $statut = fake()->randomElement(['convoque', 'confirme', 'refuse', 'en_attente']);

        return [
            'evenement_id'      => Evenement::factory(),
            'utilisateur_id'    => User::factory()->joueur(),
            'statut'            => $statut,
            'date_convocation'  => now()->subDays(fake()->numberBetween(1, 10)),
            'date_confirmation' => in_array($statut, ['confirme', 'refuse']) ? now()->subDays(fake()->numberBetween(0, 5)) : null,
        ];
    }

    public function confirme(): static
    {
        return $this->state(fn () => [
            'statut'            => 'confirme',
            'date_confirmation' => now()->subDays(fake()->numberBetween(0, 3)),
        ]);
    }

    public function convoque(): static
    {
        return $this->state(fn () => [
            'statut'            => 'convoque',
            'date_confirmation' => null,
        ]);
    }
}
