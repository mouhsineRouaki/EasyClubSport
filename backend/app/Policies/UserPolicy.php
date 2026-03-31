<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function voirProfilPresident(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->id === $utilisateur->id
            && $utilisateurConnecte->role === 'president';
    }

    public function modifierProfilPresident(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->id === $utilisateur->id
            && $utilisateurConnecte->role === 'president';
    }
}
