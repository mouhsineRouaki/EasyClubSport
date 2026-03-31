<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\User;

class EquipePolicy
{
    public function voirListe(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && $club->president_id === $utilisateur->id;
    }

    public function creer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && $club->president_id === $utilisateur->id;
    }

    public function voir(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && $equipe->club?->president_id === $utilisateur->id;
    }

    public function modifier(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && $equipe->club?->president_id === $utilisateur->id;
    }

    public function supprimer(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && $equipe->club?->president_id === $utilisateur->id;
    }

    public function gererCoach(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && $equipe->club?->president_id === $utilisateur->id;
    }

    public function gererJoueurs(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && $equipe->club?->president_id === $utilisateur->id;
    }
}
