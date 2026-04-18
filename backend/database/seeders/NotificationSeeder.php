<?php

namespace Database\Seeders;

use App\Models\Convocation;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $convocations = Convocation::with('evenement')->take(60)->get();

        foreach ($convocations as $conv) {
            Notification::create([
                'utilisateur_id'    => $conv->utilisateur_id,
                'evenement_id'      => $conv->evenement_id,
                'titre'             => 'Vous êtes convoqué(e)',
                'contenu'           => 'Convocation pour : ' . ($conv->evenement->titre ?? 'un événement'),
                'type_notification' => 'convocation',
                'est_lue'           => fake()->boolean(50),
                'date_lecture'      => null,
            ]);
        }

        $joueurs = User::where('role', 'joueur')->take(20)->get();
        $infoMessages = [
            ['Nouveau planning disponible', 'Le planning de la semaine prochaine a été publié.', 'info'],
            ['Changement d\'horaire', 'L\'entraînement de demain est décalé à 18h30.', 'alerte'],
            ['Message de votre coach', 'Votre coach vous a envoyé un message.', 'message'],
            ['Mise à jour du calendrier', 'Le calendrier des matchs a été mis à jour.', 'info'],
        ];

        foreach ($joueurs as $joueur) {
            foreach (array_rand($infoMessages, 2) as $idx) {
                [$titre, $contenu, $type] = $infoMessages[$idx];
                Notification::create([
                    'utilisateur_id'    => $joueur->id,
                    'evenement_id'      => null,
                    'titre'             => $titre,
                    'contenu'           => $contenu,
                    'type_notification' => $type,
                    'est_lue'           => fake()->boolean(40),
                    'date_lecture'      => null,
                ]);
            }
        }
    }
}
