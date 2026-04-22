<?php

namespace App\Services\Coach\Dashboard;

use App\Models\User;
use App\Repositories\Coach\Dashboard\DashboardCoachRepository;

class DashboardCoachService
{
    public function __construct(
        protected DashboardCoachRepository $dashboardCoachRepository
    ) {
    }

    public function recupererDashboard(User $utilisateur): array
    {
        $equipe = $this->dashboardCoachRepository->recupererEquipeActiveCoach($utilisateur);

        return [
            'equipe' => $equipe,
            'equipes_total' => $this->dashboardCoachRepository->compterEquipes($utilisateur),
            'joueurs_total' => $this->dashboardCoachRepository->compterJoueurs($utilisateur),
            'evenements_a_venir_total' => $this->dashboardCoachRepository->compterEvenementsAVenir($utilisateur),
            'convocations_en_attente_total' => $this->dashboardCoachRepository->compterConvocationsEnAttente($utilisateur),
            'prochain_evenement' => $this->dashboardCoachRepository->prochainEvenement($utilisateur),
            'evenements_recents' => $this->dashboardCoachRepository->listerEvenementsRecents($utilisateur),
            'canaux_recents' => $this->dashboardCoachRepository->listerCanauxRecents($utilisateur),
        ];
    }
}
