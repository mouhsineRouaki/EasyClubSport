<?php

namespace App\Repositories\Joueur\Evenement;

use App\Models\Disponibilite;
use App\Models\Evenement;
use App\Models\User;

class EvenementJoueurRepository
{
    public function listerEvenements(User $utilisateur, ?int $equipeId)
    {
        if (! $equipeId) {
            return collect();
        }

        return Evenement::query()
            ->where('equipe_id', $equipeId)
            ->with([
                'equipe.club',
                'adversaireEquipe.club',
                'disponibilites' => fn ($query) => $query->where('utilisateur_id', $utilisateur->id),
                'convocations' => fn ($query) => $query->where('utilisateur_id', $utilisateur->id),
            ])
            ->orderBy('date_debut')
            ->get();
    }

    public function recupererEvenementAvecComposition(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch.compositions.utilisateur',
            'equipe.membreEquipes.utilisateur',
        ]);
    }

    public function recupererEvenementAvecFeuilleMatch(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch',
        ]);
    }

    public function recupererEvenementAvecStatistiques(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch.statistiquesMatchs.utilisateur',
        ]);
    }

    public function enregistrerDisponibilite(User $utilisateur, Evenement $evenement, array $donnees): Disponibilite
    {
        return Disponibilite::updateOrCreate(
            [
                'utilisateur_id' => $utilisateur->id,
                'evenement_id' => $evenement->id,
            ],
            [
                'reponse' => $donnees['reponse'],
                'commentaire' => $donnees['commentaire'] ?? null,
                'date_reponse' => now(),
            ]
        )->fresh(['evenement.equipe.club', 'utilisateur']);
    }

    public function prochainEvenement(?int $equipeId): ?Evenement
    {
        if (! $equipeId) {
            return null;
        }

        return Evenement::query()
            ->where('equipe_id', $equipeId)
            ->where('date_debut', '>=', now())
            ->where('statut', '!=', 'annule')
            ->with('equipe.club')
            ->orderBy('date_debut')
            ->first();
    }

    public function listerEvenementsRecentsEquipe(?int $equipeId, int $limite = 4)
    {
        if (! $equipeId) {
            return collect();
        }

        return Evenement::query()
            ->where('equipe_id', $equipeId)
            ->with(['equipe.club'])
            ->orderBy('date_debut')
            ->limit($limite)
            ->get();
    }
}
