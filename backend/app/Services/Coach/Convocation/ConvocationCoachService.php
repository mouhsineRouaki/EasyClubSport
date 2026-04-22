<?php

namespace App\Services\Coach\Convocation;

use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use App\Repositories\Coach\Convocation\ConvocationCoachRepository;
use App\Repositories\Coach\Equipe\EquipeCoachRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ConvocationCoachService
{
    public function __construct(
        protected ConvocationCoachRepository $convocationCoachRepository,
        protected EquipeCoachRepository $equipeCoachRepository,
        protected NotificationService $notificationService
    ) {
    }

    public function listerConvocationsEquipe(User $utilisateur, Equipe $equipe)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->convocationCoachRepository->listerConvocationsEquipe($equipe);
    }

    public function creerConvocations(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees)
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);

        $joueursIds = $this->equipeCoachRepository->listerJoueursEquipe($equipe)
            ->pluck('utilisateur_id')
            ->map(fn($id) => (int) $id)
            ->all();

        foreach ($donnees['utilisateur_ids'] as $utilisateurId) {
            if (!in_array((int) $utilisateurId, $joueursIds, true)) {
                throw ValidationException::withMessages([
                    'utilisateur_ids' => 'Tous les utilisateurs convoques doivent appartenir a cette equipe.',
                ]);
            }
        }

        $statut = $donnees['statut'] ?? 'convoque';

        $convocations = collect($donnees['utilisateur_ids'])
            ->map(fn($utilisateurId) => $this->convocationCoachRepository->creerOuMettreAJourConvocation($evenement, (int) $utilisateurId, $statut))
            ->values();

        $this->notificationService->notifierConvocationsCrees($evenement, $convocations);

        return $convocations;
    }

    public function modifierConvocation(User $utilisateur, Convocation $convocation, array $donnees): Convocation
    {
        if ((int) $convocation->evenement?->equipe?->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que les convocations de vos equipes.');
        }

        return $this->convocationCoachRepository->mettreAJourConvocation($convocation, $donnees);
    }

    protected function verifierAccesEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement): void
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }
    }

    protected function verifierEquipeCoach(User $utilisateur, Equipe $equipe): void
    {
        if ((int) $equipe->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez gerer que vos equipes.');
        }
    }
}
