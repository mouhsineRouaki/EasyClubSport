<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isPresident();
    }

    public function voir(User $utilisateur, Club $club): bool
    {
        return $utilisateur->presidesClub($club);
    }

    public function creer(User $utilisateur): bool
    {
        return $utilisateur->isPresident();
    }

    public function modifier(User $utilisateur, Club $club): bool
    {
        return $utilisateur->presidesClub($club);
    }

    public function supprimer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->presidesClub($club);
    }
}
