<?php

namespace App\Services\President\Annonce;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;
use App\Repositories\President\Annonce\AnnonceRepository;

class AnnonceService
{
    public function __construct(
        protected AnnonceRepository $annonceRepository
    ) {
    }

    public function lister(User $utilisateur)
    {
        return $this->annonceRepository->listerParPresident($utilisateur);
    }

    public function listerParClub(Club $club)
    {
        return $this->annonceRepository->listerParClub($club);
    }

    public function creer(User $utilisateur, Club $club, array $donnees): Annonce
    {
        $donnees['club_id'] = $club->id;
        $donnees['auteur_id'] = $utilisateur->id;
        $donnees['est_active'] = $donnees['est_active'] ?? true;

        return $this->annonceRepository->creer($donnees);
    }

    public function mettreAJour(Annonce $annonce, array $donnees): Annonce
    {
        return $this->annonceRepository->mettreAJour($annonce, $donnees);
    }

    public function supprimer(Annonce $annonce): void
    {
        $this->annonceRepository->supprimer($annonce);
    }
}
