<?php

namespace App\Policies;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;

class EvenementPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isPresident()
            || $utilisateur->isCoach()
            || $utilisateur->isJoueur();
    }

    public function creer(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club)
            || $utilisateur->coachesEquipe($equipe);
    }

    public function voir(User $utilisateur, Evenement $evenement): bool
    {
        return $utilisateur->presidesClub($evenement->equipe?->club)
            || $utilisateur->coachesEquipe($evenement->equipe)
            || $utilisateur->belongsToEquipe($evenement->equipe);
    }

    public function modifier(User $utilisateur, Evenement $evenement): bool
    {
        return $utilisateur->presidesClub($evenement->equipe?->club)
            || $utilisateur->coachesEquipe($evenement->equipe);
    }

    public function supprimer(User $utilisateur, Evenement $evenement): bool
    {
        return $this->modifier($utilisateur, $evenement);
    }

    public function repondreDisponibilite(User $utilisateur, Evenement $evenement): bool
    {
        return $utilisateur->belongsToEquipe($evenement->equipe);
    }

    public function repondreInvitationAdversaire(User $utilisateur, Evenement $evenement): bool
    {
        return $utilisateur->isCoach()
            && $utilisateur->coachesEquipe($evenement->adversaireEquipe);
    }
}
