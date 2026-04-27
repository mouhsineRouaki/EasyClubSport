<?php

namespace App\Services\Joueur\Statistique;

use App\Models\User;
use App\Repositories\Joueur\Statistique\StatistiqueJoueurRepository;

class StatistiqueJoueurService
{
    public function __construct(
        protected StatistiqueJoueurRepository $statistiqueJoueurRepository
    ) {
    }

    public function listerStatistiques(User $utilisateur)
    {
        return $this->statistiqueJoueurRepository->listerStatistiques($utilisateur);
    }
}
