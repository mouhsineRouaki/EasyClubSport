<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\Club;
use Database\Seeders\Support\SeederImageStorage;
use Illuminate\Database\Seeder;

class AnnonceSeeder extends Seeder
{
    public function run(): void
    {
        $images = new SeederImageStorage();
        $clubs = Club::with('president')->get();

        $annonces = [
            [
                'titre' => 'Reprise de la saison 2026-2027',
                'contenu' => 'Nous avons le plaisir de vous annoncer la reprise des entrainements pour la nouvelle saison. Les inscriptions sont ouvertes jusqu au 30 septembre. Retrouvez l ensemble des equipes sur le terrain a partir du 1er octobre.',
            ],
            [
                'titre' => 'Tournoi regional - Inscriptions ouvertes',
                'contenu' => 'Le club participe au tournoi regional des jeunes. Les equipes U14 et U16 sont invitees a s inscrire avant le 15 du mois. Les frais d inscription sont pris en charge par le club.',
            ],
            [
                'titre' => 'Assemblee generale annuelle',
                'contenu' => 'L assemblee generale du club se tiendra le dernier samedi du mois a 10h dans la salle de reunion. Tous les membres sont invites a y participer. Ordre du jour : bilan de la saison, elections du bureau, projets 2026.',
            ],
            [
                'titre' => 'Renouvellement des licences sportives',
                'contenu' => 'Rappel important : le renouvellement des licences est obligatoire avant le 1er novembre. Merci de vous rapprocher du secretariat avec une photo d identite et le certificat medical de moins de 3 mois.',
            ],
            [
                'titre' => 'Nouveau partenariat avec un equipementier',
                'contenu' => 'Le club a signe un partenariat avec un equipementier sportif local. Des reductions exclusives seront proposees a tous les membres sur presentation de leur carte d adherent.',
            ],
        ];

        foreach ($clubs as $club) {
            foreach ($annonces as $data) {
                Annonce::create([
                    'club_id' => $club->id,
                    'auteur_id' => $club->president_id,
                    'titre' => $data['titre'],
                    'contenu' => $data['contenu'],
                    'image' => $images->annonceImage($club->nom . ' ' . $data['titre']),
                    'est_active' => true,
                ]);
            }
        }
    }
}
