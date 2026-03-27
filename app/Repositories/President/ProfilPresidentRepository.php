<?php

namespace App\Repositories\President;

use App\Models\User;

class ProfilPresidentRepository
{
    public function mettreAJour(User $utilisateur, array $donnees): User
    {
        $utilisateur->update($donnees);

        return $utilisateur->fresh();
    }
}
