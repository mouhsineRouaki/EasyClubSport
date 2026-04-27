<?php

namespace App\Services\Joueur\Annonce;

use App\Models\User;
use App\Repositories\Joueur\Annonce\AnnonceJoueurRepository;

class AnnonceJoueurService
{
    public function __construct(
        protected AnnonceJoueurRepository $annonceJoueurRepository
    ) {
    }

    public function listerAnnonces(User $utilisateur, array $filtres = [])
    {
        return $this->annonceJoueurRepository->listerAnnonces($utilisateur, $filtres);
    }
}
