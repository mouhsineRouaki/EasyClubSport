<?php

namespace App\Services\President;

use App\Models\User;
use App\Repositories\President\ProfilPresidentRepository;

class ProfilPresidentService
{
    public function __construct(
        protected ProfilPresidentRepository $profilPresidentRepository
    ) {
    }

    public function mettreAJour(User $utilisateur, array $donnees): User
    {
        $donnees['name'] = trim($donnees['prenom'].' '.$donnees['nom']);

        return $this->profilPresidentRepository->mettreAJour($utilisateur, $donnees);
    }
}
