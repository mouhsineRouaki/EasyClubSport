<?php

namespace App\Services\Joueur\Dashboard;

use App\Models\User;
use App\Repositories\Joueur\Convocation\ConvocationJoueurRepository;
use App\Repositories\Joueur\Dashboard\DashboardJoueurRepository;
use App\Repositories\Joueur\Document\DocumentJoueurRepository;
use App\Repositories\Joueur\Equipe\EquipeJoueurRepository;
use App\Repositories\Joueur\Evenement\EvenementJoueurRepository;
use App\Repositories\Joueur\Messagerie\MessagerieJoueurRepository;
use App\Repositories\Joueur\Notification\NotificationJoueurRepository;

class DashboardJoueurService
{
    public function __construct(
        protected EquipeJoueurRepository $equipeJoueurRepository,
        protected EvenementJoueurRepository $evenementJoueurRepository,
        protected ConvocationJoueurRepository $convocationJoueurRepository,
        protected NotificationJoueurRepository $notificationJoueurRepository,
        protected DashboardJoueurRepository $dashboardJoueurRepository,
        protected DocumentJoueurRepository $documentJoueurRepository,
        protected MessagerieJoueurRepository $messagerieJoueurRepository
    ) {
    }

    public function recupererDashboard(User $utilisateur): array
    {
        $equipe = $this->equipeJoueurRepository->recupererEquipeActive($utilisateur);

        return [
            'equipe' => $equipe,
            'prochain_evenement' => $this->evenementJoueurRepository->prochainEvenement($equipe?->id),
            'convocations_en_attente_total' => $this->convocationJoueurRepository->compterConvocationsEnAttente($utilisateur),
            'notifications_non_lues_total' => $this->notificationJoueurRepository->compterNotificationsNonLues($utilisateur),
            'evenements' => $this->evenementJoueurRepository->listerEvenementsRecentsEquipe($equipe?->id),
            'convocations' => $this->convocationJoueurRepository->listerConvocationsRecentes($utilisateur),
            'dernieres_annonces' => $this->dashboardJoueurRepository->listerAnnoncesEquipe($equipe?->club_id),
            'derniers_documents' => $this->documentJoueurRepository->listerDocumentsRecents($utilisateur),
            'derniers_canaux' => $this->messagerieJoueurRepository->listerCanauxRecents($utilisateur),
        ];
    }
}
