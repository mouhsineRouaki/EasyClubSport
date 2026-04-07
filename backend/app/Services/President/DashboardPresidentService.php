<?php

namespace App\Services\President;

use App\Models\User;
use App\Repositories\President\DashboardPresidentRepository;

class DashboardPresidentService
{
    public function __construct(
        protected DashboardPresidentRepository $dashboardPresidentRepository
    ) {
    }

    public function recuperer(User $utilisateur): array
    {
        return [
            'statistiques' => [
                'clubs_total' => $this->dashboardPresidentRepository->compterClubs($utilisateur),
                'equipes_total' => $this->dashboardPresidentRepository->compterEquipes($utilisateur),
                'coachs_total' => $this->dashboardPresidentRepository->compterCoachs($utilisateur),
                'joueurs_total' => $this->dashboardPresidentRepository->compterJoueurs($utilisateur),
                'evenements_a_venir_total' => $this->dashboardPresidentRepository->compterEvenementsAVenir($utilisateur),
                'evenements_passes_total' => $this->dashboardPresidentRepository->compterEvenementsPasses($utilisateur),
                'cotisations_payees_total' => $this->dashboardPresidentRepository->compterCotisationsPayees($utilisateur),
                'cotisations_en_attente_total' => $this->dashboardPresidentRepository->compterCotisationsEnAttente($utilisateur),
            ],
            'clubs_recents' => $this->dashboardPresidentRepository->recupererClubsRecents($utilisateur),
            'equipes_recentes' => $this->dashboardPresidentRepository->recupererEquipesRecentes($utilisateur),
            'prochains_evenements' => $this->dashboardPresidentRepository->recupererProchainsEvenements($utilisateur),
            'dernieres_annonces' => $this->dashboardPresidentRepository->recupererDernieresAnnonces($utilisateur),
        ];
    }
}
