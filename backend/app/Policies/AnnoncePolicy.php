<?php

namespace App\Policies;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;

class AnnoncePolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isPresident();
    }

    public function creer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->presidesClub($club);
    }

    public function voir(User $utilisateur, Annonce $annonce): bool
    {
        return $utilisateur->presidesClub($annonce->club);
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
