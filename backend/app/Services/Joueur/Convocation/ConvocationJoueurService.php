<?php

namespace App\Services\Joueur\Convocation;

use App\Models\Convocation;
use App\Models\User;
use App\Repositories\Joueur\Convocation\ConvocationJoueurRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;

class ConvocationJoueurService
{
    public function __construct(
        protected ConvocationJoueurRepository $convocationJoueurRepository,
        protected NotificationService $notificationService
    ) {
    }

    public function listerConvocations(User $utilisateur)
    {
        return $this->convocationJoueurRepository->listerConvocations($utilisateur);
    }

    public function repondreConvocation(User $utilisateur, Convocation $convocation, array $donnees): Convocation
    {
        if ((int) $convocation->utilisateur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Cette convocation ne vous appartient pas.');
        }

        $convocation = $this->convocationJoueurRepository->mettreAJourConvocation($convocation, $donnees);
        $this->notificationService->notifierReponseConvocation($convocation);

        return $convocation;
    }
}
