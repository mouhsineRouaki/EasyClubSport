<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function voirProfil(User $utilisateurConnecte, User $utilisateur): bool
    {
        return (int) $utilisateurConnecte->id === (int) $utilisateur->id;
    }

    public function modifierProfil(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $this->voirProfil($utilisateurConnecte, $utilisateur);
    }

    public function voirProfilPresident(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->isPresident()
            && $this->voirProfil($utilisateurConnecte, $utilisateur);
    }

    public function modifierProfilPresident(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->isPresident()
            && $this->modifierProfil($utilisateurConnecte, $utilisateur);
    }

    public function voirProfilCoach(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->isCoach()
            && $this->voirProfil($utilisateurConnecte, $utilisateur);
    }

    public function modifierProfilCoach(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->isCoach()
            && $this->modifierProfil($utilisateurConnecte, $utilisateur);
    }

    public function voirProfilJoueur(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->isJoueur()
            && $this->voirProfil($utilisateurConnecte, $utilisateur);
    }

    public function modifierProfilJoueur(User $utilisateurConnecte, User $utilisateur): bool
    {
        return $utilisateurConnecte->isJoueur()
            && $this->modifierProfil($utilisateurConnecte, $utilisateur);
    }
}
