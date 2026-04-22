<?php

namespace App\Services\Joueur\Evenement;

use App\Models\Evenement;
use App\Models\User;
use App\Repositories\Joueur\Equipe\EquipeJoueurRepository;
use App\Repositories\Joueur\Evenement\EvenementJoueurRepository;
use App\Services\Notification\NotificationService;
use App\Support\CompositionMatchPresenter;
use App\Support\FeuilleMatchPresenter;
use App\Support\StatistiqueMatchPresenter;
use Illuminate\Auth\Access\AuthorizationException;

class EvenementJoueurService
{
    public function __construct(
        protected EvenementJoueurRepository $evenementJoueurRepository,
        protected EquipeJoueurRepository $equipeJoueurRepository,
        protected NotificationService $notificationService
    ) {
    }

    public function listerEvenements(User $utilisateur)
    {
        $equipe = $this->equipeJoueurRepository->recupererEquipeActive($utilisateur);

        return $this->evenementJoueurRepository->listerEvenements($utilisateur, $equipe?->id);
    }

    public function recupererCompositionMatch(User $utilisateur, Evenement $evenement): array
    {
        $this->verifierAccesEvenement($utilisateur, $evenement, 'Vous ne pouvez voir que la composition des matchs de votre equipe.');

        $evenement = $this->evenementJoueurRepository->recupererEvenementAvecComposition($evenement);

        return CompositionMatchPresenter::depuisEvenement($evenement) ?? [];
    }

    public function recupererFeuilleMatch(User $utilisateur, Evenement $evenement): ?array
    {
        $this->verifierAccesEvenement($utilisateur, $evenement, 'Vous ne pouvez voir que la feuille de match de votre equipe.');

        $evenement = $this->evenementJoueurRepository->recupererEvenementAvecFeuilleMatch($evenement);

        return FeuilleMatchPresenter::depuisEvenement($evenement);
    }

    public function recupererStatistiquesMatchEvenement(User $utilisateur, Evenement $evenement): array
    {
        $this->verifierAccesEvenement($utilisateur, $evenement, 'Vous ne pouvez voir que les statistiques des matchs de votre equipe.');

        $evenement = $this->evenementJoueurRepository->recupererEvenementAvecStatistiques($evenement);

        return StatistiqueMatchPresenter::depuisEvenement($evenement);
    }

    public function repondreDisponibilite(User $utilisateur, Evenement $evenement, array $donnees)
    {
        $this->verifierAccesEvenement($utilisateur, $evenement, 'Vous ne pouvez repondre qu aux evenements de votre equipe.');

        $disponibilite = $this->evenementJoueurRepository->enregistrerDisponibilite($utilisateur, $evenement, $donnees);
        $this->notificationService->notifierDisponibiliteMiseAJour($disponibilite);

        return $disponibilite;
    }

    protected function verifierAccesEvenement(User $utilisateur, Evenement $evenement, string $message): void
    {
        $equipe = $this->equipeJoueurRepository->recupererEquipeActive($utilisateur);

        if (! $equipe || (int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException($message);
        }
    }
}
