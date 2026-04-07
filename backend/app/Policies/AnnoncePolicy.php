<?php

namespace App\Policies;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;

class AnnoncePolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function creer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && (int) $club->president_id === (int) $utilisateur->id;
    }

    public function voir(User $utilisateur, Annonce $annonce): bool
    {
        return $utilisateur->role === 'president'
            && (int) $annonce->club?->president_id === (int) $utilisateur->id;
    }

    public function modifier(User $utilisateur, Annonce $annonce): bool
    {
        return $this->voir($utilisateur, $annonce);
    }

    public function supprimer(User $utilisateur, Annonce $annonce): bool
    {
        return $this->voir($utilisateur, $annonce);
    }
}
