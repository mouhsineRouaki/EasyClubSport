<?php

namespace App\Policies;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;

class EvenementPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function creer(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && $equipe->club?->president_id === $utilisateur->id;
    }

    public function voir(User $utilisateur, Evenement $evenement): bool
    {
        return $utilisateur->role === 'president'
            && $evenement->equipe?->club?->president_id === $utilisateur->id;
    }

    public function modifier(User $utilisateur, Evenement $evenement): bool
    {
        return $this->voir($utilisateur, $evenement);
    }

    public function supprimer(User $utilisateur, Evenement $evenement): bool
    {
        return $this->voir($utilisateur, $evenement);
    }
}
