<?php

namespace App\Repositories\Coach\Profil;

use App\Models\User;

class ProfilCoachRepository
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
