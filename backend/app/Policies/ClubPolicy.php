<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function voir(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && $club->president_id === $utilisateur->id;
    }

    public function creer(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function modifier(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && $club->president_id === $utilisateur->id;
    }

    public function supprimer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && $club->president_id === $utilisateur->id;
    }
}
