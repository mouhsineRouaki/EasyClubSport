<?php

namespace App\Services\Coach\Messagerie;

use App\Models\Canal;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Coach\Messagerie\MessagerieCoachRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;

class MessagerieCoachService
{
    public function __construct(
        protected MessagerieCoachRepository $messagerieCoachRepository,
        protected NotificationService $notificationService
    ) {
    }

    public function listerCanaux(User $utilisateur)
    {
        return $this->messagerieCoachRepository->listerCanaux($utilisateur);
    }

    public function listerMessages(User $utilisateur, Canal $canal)
    {
        $this->verifierCanalCoach($utilisateur, $canal);

        return $this->messagerieCoachRepository->listerMessagesParCanal($canal);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $this->verifierCanalCoach($utilisateur, $canal);

        $message = $this->messagerieCoachRepository->creerMessage($utilisateur, $canal, $donnees);
        $this->notificationService->notifierNouveauMessage($message);

        return $message;
    }

    public function modifierMessage(User $utilisateur, Message $message, array $donnees): Message
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que vos propres messages.');
        }

        if ((int) $message->equipe?->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que les messages de vos equipes.');
        }

        return $this->messagerieCoachRepository->mettreAJourMessage($message, $donnees);
    }

    public function supprimerMessage(User $utilisateur, Message $message): void
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez supprimer que vos propres messages.');
        }

        if ((int) $message->equipe?->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez supprimer que les messages de vos equipes.');
        }

        $this->messagerieCoachRepository->supprimerMessage($message);
    }

    protected function verifierCanalCoach(User $utilisateur, Canal $canal): void
    {
        if (! $this->messagerieCoachRepository->coachPeutVoirCanal($utilisateur, $canal)) {
            throw new AuthorizationException('Vous n avez pas acces a ce canal.');
        }
    }
}
