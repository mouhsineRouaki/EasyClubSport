<?php

namespace Database\Factories;

use App\Models\Equipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        $messages = [
            'Bonne séance à tous ce soir !',
            'N\'oubliez pas le match de samedi.',
            'Rendez-vous à 18h sur le terrain.',
            'Félicitations pour la victoire !',
            'Entraînement annulé ce jeudi.',
            'Rappel : réunion technique demain.',
            'Bravo à toute l\'équipe pour la performance.',
            'Le bus part à 14h30 pour le déplacement.',
            'Pensez à ramener vos licences.',
            'Super match, continuez comme ça !',
        ];

        return [
            'equipe_id'    => Equipe::factory(),
            'expediteur_id' => User::factory(),
            'contenu'      => fake()->randomElement($messages),
            'type_message' => 'equipe',
        ];
    }
}
