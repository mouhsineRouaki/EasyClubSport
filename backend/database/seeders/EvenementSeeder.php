<?php

namespace Database\Seeders;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvenementSeeder extends Seeder
{
    public function run(): void
    {
        $equipes = Equipe::with('coach')->get();

        foreach ($equipes as $equipe) {
            $coach = $equipe->coach ?? User::where('role', 'coach')->first();

            $matchsPasses = [
                ['Match vs Club Ourika', 'match', now()->subDays(20), 'planifie', 'Stade Municipal'],
                ['Match vs FC Oasis', 'match', now()->subDays(8), 'termine', 'Terrain Annexe'],
            ];
            foreach ($matchsPasses as [$titre, $type, $date, $statut, $lieu]) {
                Evenement::create([
                    'equipe_id'   => $equipe->id,
                    'createur_id' => $coach?->id,
                    'titre'       => $titre . ' - ' . $equipe->nom,
                    'type'        => $type,
                    'date_debut'  => $date,
                    'date_fin'    => (clone $date)->modify('+2 hours'),
                    'lieu'        => $lieu,
                    'adversaire'  => explode(' vs ', $titre)[1] ?? null,
                    'statut'      => $statut,
                    'description' => 'Rencontre officielle du championnat régional.',
                ]);
            }

            $entrainements = [
                ['Entraînement physique', now()->subDays(15), 'termine'],
                ['Entraînement tactique', now()->subDays(10), 'termine'],
                ['Entraînement technique', now()->subDays(3), 'planifie'],
                ['Séance collective', now()->addDays(4), 'planifie'],
                ['Entraînement intensif', now()->addDays(11), 'planifie'],
            ];
            foreach ($entrainements as [$titre, $date, $statut]) {
                Evenement::create([
                    'equipe_id'   => $equipe->id,
                    'createur_id' => $coach?->id,
                    'titre'       => $titre,
                    'type'        => 'entrainement',
                    'date_debut'  => $date,
                    'date_fin'    => (clone $date)->modify('+1 hour 30 minutes'),
                    'lieu'        => 'Terrain d\'entraînement',
                    'adversaire'  => null,
                    'statut'      => $statut,
                    'description' => 'Séance d\'entraînement hebdomadaire.',
                ]);
            }

            Evenement::create([
                'equipe_id'   => $equipe->id,
                'createur_id' => $coach?->id,
                'titre'       => 'Réunion mensuelle d\'équipe',
                'type'        => 'reunion',
                'date_debut'  => now()->addDays(7),
                'date_fin'    => now()->addDays(7)->modify('+1 hour'),
                'lieu'        => 'Salle de réunion du club',
                'adversaire'  => null,
                'statut'      => 'planifie',
                'description' => 'Bilan du mois et préparation du prochain cycle.',
            ]);

            Evenement::create([
                'equipe_id'   => $equipe->id,
                'createur_id' => $coach?->id,
                'titre'       => 'Match officiel ' . $equipe->categorie,
                'type'        => 'match',
                'date_debut'  => now()->addDays(14),
                'date_fin'    => now()->addDays(14)->modify('+2 hours'),
                'lieu'        => 'Complexe Sportif',
                'adversaire'  => 'Club ' . fake('fr_FR')->lastName(),
                'statut'      => 'planifie',
                'description' => 'Match comptant pour le championnat.',
            ]);
        }
    }
}
