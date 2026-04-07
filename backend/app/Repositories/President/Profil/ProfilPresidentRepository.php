<?php

namespace App\Repositories\President\Profil;

use App\Models\User;

class ProfilPresidentRepository
{
    public function mettreAJour(User $utilisateur, array $donnees): User
    {
        $utilisateur->update($donnees);

        return $utilisateur->fresh();
    }
}

