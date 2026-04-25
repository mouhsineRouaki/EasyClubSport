<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\User;

class EquipePolicy
{
    public function voirListe(User $utilisateur, Club $club): bool
    {
        return $utilisateur->presidesClub($club);
    }

    public function creer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->presidesClub($club);
    }

    public function voir(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club)
            || $utilisateur->coachesEquipe($equipe)
            || $utilisateur->belongsToEquipe($equipe);
    }

    public function modifier(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club);
    }

    public function supprimer(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club);
    }

    public function gererCoach(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club);
    }

    public function gererJoueurs(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club);
    }

    public function voirListeCoach(User $utilisateur): bool
    {
        return $utilisateur->isCoach();
    }

    public function gererCommeCoach(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->coachesEquipe($equipe);
    }

    public function voirCommeJoueur(User $utilisateur): bool
    {
        return $utilisateur->isJoueur();
    }

    public function rejoindreCommeJoueur(User $utilisateur): bool
    {
        return $utilisateur->isJoueur();
    }
}
