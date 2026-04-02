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

    public function recupererDonnees(User $president): array
    {
        $presidentId = $president->id;
        $cotisations = $this->dashboardPresidentRepository->recupererCotisations($presidentId);

        return [
            'statistiques' => [
                'clubs_total' => $this->dashboardPresidentRepository->compterClubs($presidentId),
                'equipes_total' => $this->dashboardPresidentRepository->compterEquipes($presidentId),
                'joueurs_total' => $this->dashboardPresidentRepository->compterJoueurs($presidentId),
                'coachs_total' => $this->dashboardPresidentRepository->compterCoachs($presidentId),
                'evenements_a_venir_total' => $this->dashboardPresidentRepository->compterEvenementsAVenir($presidentId),
                'evenements_passes_total' => $this->dashboardPresidentRepository->compterEvenementsPasses($presidentId),
                'cotisations_payees_total' => $cotisations['montant_paye'],
                'cotisations_en_attente_total' => $cotisations['montant_en_attente'],
            ],
            'clubs_recents' => $this->dashboardPresidentRepository->recupererClubsRecents($presidentId),
            'equipes_recentes' => $this->dashboardPresidentRepository->recupererEquipesRecentes($presidentId),
            'prochains_evenements' => $this->dashboardPresidentRepository->recupererProchainsEvenements($presidentId),
        ];
    }
}
