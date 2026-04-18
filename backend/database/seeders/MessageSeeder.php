<?php

namespace Database\Seeders;

use App\Models\Canal;
use App\Models\CanalUtilisateur;
use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $contenuMessages = [
            'Bonne séance à tous ce soir !',
            'N\'oubliez pas le match de samedi, rendez-vous à 14h.',
            'Félicitations pour la victoire d\'hier, super performance !',
            'L\'entraînement de jeudi est maintenu, terrain principal.',
            'Rappel : apportez vos équipements complets demain.',
            'Bravo à toute l\'équipe, belle progression collective.',
            'Le bus part à 13h30 pour le déplacement de dimanche.',
            'Pensez à renouveler vos licences avant fin du mois.',
            'Super match aujourd\'hui, continuez sur cette lancée !',
            'Réunion technique vendredi à 19h, salle de réunion.',
            'Planning de la semaine disponible sur le groupe.',
            'Attention changement d\'horaire : séance à 17h30 demain.',
        ];

        $canaux = Canal::with('canalUtilisateurs')->get();

        foreach ($canaux as $canal) {
            $membres = CanalUtilisateur::where('canal_id', $canal->id)->pluck('utilisateur_id');
            if ($membres->isEmpty()) continue;

            $nbMessages = rand(5, 12);
            for ($i = 0; $i < $nbMessages; $i++) {
                Message::create([
                    'equipe_id'     => $canal->equipe_id,
                    'expediteur_id' => $membres->random(),
                    'contenu'       => fake()->randomElement($contenuMessages),
                    'type_message'  => 'equipe',
                    'created_at'    => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
