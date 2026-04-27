<?php

namespace App\Services\Coach\Messagerie;

use App\Models\Canal;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Coach\Messagerie\MessagerieCoachRepository;
use App\Services\Notification\NotificationService;
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
        return $this->messagerieCoachRepository->listerMessagesParCanal($canal);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $message = $this->messagerieCoachRepository->creerMessage($utilisateur, $canal, $donnees);
        $this->notificationService->notifierNouveauMessage($message);

        return $message;
    }

    public function modifierMessage(User $utilisateur, Message $message, array $donnees): Message
    {
        return $this->messagerieCoachRepository->mettreAJourMessage($message, $donnees);
    }

    public function supprimerMessage(User $utilisateur, Message $message): void
    {
        $this->messagerieCoachRepository->supprimerMessage($message);
    }
}
