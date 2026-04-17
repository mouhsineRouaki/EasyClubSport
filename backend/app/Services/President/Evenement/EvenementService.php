<?php

namespace App\Services\President\Evenement;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use App\Repositories\President\Evenement\EvenementRepository;
use App\Services\Evenement\MatchInvitationService;

class EvenementService
{
    public function __construct(
        protected EvenementRepository $evenementRepository,
        protected MatchInvitationService $matchInvitationService
    ) {
    }

    public function lister(User $utilisateur, array $filtres = [])
    {
        return $this->evenementRepository->listerParPresident($utilisateur, $filtres);
    }

    public function listerParEquipe(Equipe $equipe, array $filtres = [])
    {
        return $this->evenementRepository->listerParEquipe($equipe, $filtres);
    }

    public function creer(User $utilisateur, Equipe $equipe, array $donnees): Evenement
    {
        $donnees['equipe_id'] = $equipe->id;
        $donnees['createur_id'] = $utilisateur->id;
        $donnees['statut'] = $donnees['statut'] ?? 'planifie';
        $donnees = $this->synchroniserAdversaire($donnees);
        $donnees = $this->preparerInvitationAdversaire($donnees);

        $evenement = $this->evenementRepository->creer($donnees);
        $this->matchInvitationService->notifierDemande($evenement);

        return $evenement;
    }

    public function mettreAJour(Evenement $evenement, array $donnees): Evenement
    {
        $ancienType = $evenement->type;
        $ancienAdversaireEquipeId = $evenement->adversaire_equipe_id;
        $donnees = $this->synchroniserAdversaire($donnees, $evenement);
        $invitationChangee = $this->invitationAdversaireChangee($donnees, $ancienType, $ancienAdversaireEquipeId);
        $donnees = $this->preparerInvitationAdversaire($donnees, $evenement, $invitationChangee);

        $evenement = $this->evenementRepository->mettreAJour($evenement, $donnees);

        if ($invitationChangee) {
            $this->matchInvitationService->notifierDemande($evenement);
        }

        return $evenement;
    }

    public function repondreInvitationAdversaire(User $utilisateur, Evenement $evenement, string $decision): Evenement
    {
        return $this->matchInvitationService->repondre($utilisateur, $evenement, $decision);
    }

    public function supprimer(Evenement $evenement): void
    {
        $this->evenementRepository->supprimer($evenement);
    }

    protected function synchroniserAdversaire(array $donnees, ?Evenement $evenement = null): array
    {
        $type = $donnees['type'] ?? $evenement?->type;

        if ($type !== 'match') {
            $donnees['adversaire'] = null;
            $donnees['adversaire_equipe_id'] = null;

            return $donnees;
        }

        $adversaireEquipeId = $donnees['adversaire_equipe_id'] ?? $evenement?->adversaire_equipe_id;

        if ($adversaireEquipeId) {
            $donnees['adversaire'] = Equipe::query()
                ->whereKey($adversaireEquipeId)
                ->value('nom');
        }

        return $donnees;
    }

    protected function preparerInvitationAdversaire(array $donnees, ?Evenement $evenement = null, bool $forcerNouvelleDemande = false): array
    {
        $type = $donnees['type'] ?? $evenement?->type;

        if ($type !== 'match') {
            $donnees['statut_invitation_adversaire'] = 'sans_invitation';
            $donnees['invitation_adversaire_repondue_par_id'] = null;
            $donnees['invitation_adversaire_repondue_at'] = null;

            return $donnees;
        }

        if (! $evenement || $forcerNouvelleDemande) {
            $donnees['statut_invitation_adversaire'] = 'en_attente';
            $donnees['invitation_adversaire_repondue_par_id'] = null;
            $donnees['invitation_adversaire_repondue_at'] = null;
        }

        return $donnees;
    }

    protected function invitationAdversaireChangee(array $donnees, ?string $ancienType, ?int $ancienAdversaireEquipeId): bool
    {
        $nouveauType = $donnees['type'] ?? $ancienType;
        $nouvelAdversaireEquipeId = array_key_exists('adversaire_equipe_id', $donnees)
            ? $donnees['adversaire_equipe_id']
            : $ancienAdversaireEquipeId;

        if ($nouveauType !== 'match') {
            return $ancienType === 'match';
        }

        return $ancienType !== 'match' || (int) $nouvelAdversaireEquipeId !== (int) $ancienAdversaireEquipeId;
    }
}

