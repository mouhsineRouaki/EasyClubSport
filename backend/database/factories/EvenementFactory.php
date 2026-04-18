<?php

namespace Database\Factories;

use App\Models\Equipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvenementFactory extends Factory
{
    public function definition(): array
    {
        $type      = fake()->randomElement(['match', 'entrainement', 'reunion']);
        $dateDebut = fake()->dateTimeBetween('-1 month', '+2 months');
        $dateFin   = (clone $dateDebut)->modify('+' . fake()->numberBetween(1, 3) . ' hours');

        $lieux = ['Stade Municipal', 'Gymnase Central', 'Terrain Annexe', 'Complexe Sportif', 'Salle Omnisports'];

        return [
            'equipe_id'   => Equipe::factory(),
            'createur_id' => User::factory()->coach(),
            'titre'       => $this->titreEvenement($type),
            'type'        => $type,
            'date_debut'  => $dateDebut,
            'date_fin'    => $dateFin,
            'lieu'        => fake()->randomElement($lieux),
            'adversaire'  => $type === 'match' ? 'Club ' . fake('fr_FR')->lastName() : null,
            'description' => fake('fr_FR')->sentence(),
            'statut'      => fake()->randomElement(['planifie', 'planifie', 'planifie', 'termine', 'annule']),
        ];
    }

    public function match(): static
    {
        return $this->state(fn () => [
            'type'       => 'match',
            'titre'      => 'Match vs Club ' . fake('fr_FR')->lastName(),
            'adversaire' => 'Club ' . fake('fr_FR')->lastName(),
        ]);
    }

    public function entrainement(): static
    {
        return $this->state(fn () => [
            'type'       => 'entrainement',
            'titre'      => 'Entraînement ' . fake()->randomElement(['tactique', 'physique', 'technique', 'collectif']),
            'adversaire' => null,
        ]);
    }

    public function planifie(): static
    {
        return $this->state(fn () => [
            'statut'     => 'planifie',
            'date_debut' => fake()->dateTimeBetween('now', '+2 months'),
        ]);
    }

    private function titreEvenement(string $type): string
    {
        return match ($type) {
            'match'        => 'Match vs Club ' . fake('fr_FR')->lastName(),
            'entrainement' => 'Entraînement ' . fake()->randomElement(['tactique', 'physique', 'technique']),
            'reunion'      => 'Réunion ' . fake()->randomElement(['d\'équipe', 'technique', 'mensuelle']),
            default        => 'Événement sportif',
        };
    }
}
