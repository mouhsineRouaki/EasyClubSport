<?php

namespace App\Services\President\Evenement;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use App\Repositories\President\Evenement\EvenementRepository;

class EvenementService
{
    public function __construct(
        protected EvenementRepository $evenementRepository
    ) {
    }

    public function lister(User $utilisateur)
    {
        return $this->evenementRepository->listerParPresident($utilisateur);
    }

    public function listerParEquipe(Equipe $equipe)
    {
        return $this->evenementRepository->listerParEquipe($equipe);
    }

    public function creer(User $utilisateur, Equipe $equipe, array $donnees): Evenement
    {
        $donnees['equipe_id'] = $equipe->id;
        $donnees['createur_id'] = $utilisateur->id;
        $donnees['statut'] = $donnees['statut'] ?? 'planifie';

        return $this->evenementRepository->creer($donnees);
    }

    public function mettreAJour(Evenement $evenement, array $donnees): Evenement
    {
        return $this->evenementRepository->mettreAJour($evenement, $donnees);
    }

    public function supprimer(Evenement $evenement): void
    {
        $this->evenementRepository->supprimer($evenement);
    }
}

