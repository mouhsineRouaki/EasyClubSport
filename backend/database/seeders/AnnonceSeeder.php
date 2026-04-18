<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnonceSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = Club::with('president')->get();

        $annonces = [
            [
                'titre'   => 'Reprise de la saison 2024-2025',
                'contenu' => 'Nous avons le plaisir de vous annoncer la reprise des entraînements pour la nouvelle saison. Les inscriptions sont ouvertes jusqu\'au 30 septembre. Retrouvez l\'ensemble des équipes sur le terrain à partir du 1er octobre.',
            ],
            [
                'titre'   => 'Tournoi régional — Inscriptions ouvertes',
                'contenu' => 'Le club participe au tournoi régional des jeunes. Les équipes U14 et U16 sont invitées à s\'inscrire avant le 15 du mois. Les frais d\'inscription sont pris en charge par le club.',
            ],
            [
                'titre'   => 'Assemblée générale annuelle',
                'contenu' => 'L\'assemblée générale du club se tiendra le dernier samedi du mois à 10h dans la salle de réunion. Tous les membres sont invités à y participer. Ordre du jour : bilan de la saison, élections du bureau, projets 2025.',
            ],
            [
                'titre'   => 'Renouvellement des licences sportives',
                'contenu' => 'Rappel important : le renouvellement des licences est obligatoire avant le 1er novembre. Merci de vous rapprocher du secrétariat avec une photo d\'identité et le certificat médical de moins de 3 mois.',
            ],
            [
                'titre'   => 'Nouveau partenariat avec un équipementier',
                'contenu' => 'Le club a signé un partenariat avec un équipementier sportif local. Des réductions exclusives seront proposées à tous les membres sur présentation de leur carte d\'adhérent.',
            ],
        ];

        foreach ($clubs as $club) {
            foreach ($annonces as $data) {
                Annonce::create([
                    'club_id'    => $club->id,
                    'auteur_id'  => $club->president_id,
                    'titre'      => $data['titre'],
                    'contenu'    => $data['contenu'],
                    'image'      => null,
                    'est_active' => true,
                ]);
            }
        }
    }
}
