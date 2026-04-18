<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['info', 'alerte', 'message', 'convocation']);

        $titres = [
            'info'        => ['Nouveau planning disponible', 'Mise à jour du calendrier', 'Information importante'],
            'alerte'      => ['Entraînement annulé', 'Changement de lieu', 'Alerte météo'],
            'message'     => ['Nouveau message de votre coach', 'Message de l\'équipe', 'Réponse à votre message'],
            'convocation' => ['Vous êtes convoqué pour le match', 'Convocation pour l\'entraînement', 'Convocation réunion d\'équipe'],
        ];

        return [
            'utilisateur_id'    => User::factory(),
            'evenement_id'      => null,
            'titre'             => fake()->randomElement($titres[$type]),
            'contenu'           => fake('fr_FR')->sentence(),
            'type_notification' => $type,
            'action'            => null,
            'statut_action'     => null,
            'est_lue'           => fake()->boolean(40),
            'date_lecture'      => null,
        ];
    }
}
