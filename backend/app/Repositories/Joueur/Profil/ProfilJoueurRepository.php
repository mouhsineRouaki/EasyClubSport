<?php

namespace App\Repositories\Joueur\Profil;

use App\Models\User;

class ProfilJoueurRepository
{
    public function recupererProfil(User $utilisateur): User
    {
        return $utilisateur->fresh();
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees): User
    {
        $utilisateur->update($donnees);

        return $utilisateur->fresh();
    }
}
