<?php

namespace App\Services\Joueur\Messagerie;

use App\Models\Canal;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Joueur\Messagerie\MessagerieJoueurRepository;
use App\Services\Notification\NotificationService;
class MessagerieJoueurService
{
    public function __construct(
        protected MessagerieJoueurRepository $messagerieJoueurRepository,
        protected NotificationService $notificationService
    ) {
    }

    public function listerCanaux(User $utilisateur)
    {
        return $this->messagerieJoueurRepository->listerCanaux($utilisateur);
    }

    public function listerMessages(Canal $canal, User $utilisateur)
    {
        return $this->messagerieJoueurRepository->listerMessagesParCanal($canal);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $message = $this->messagerieJoueurRepository->creerMessage($utilisateur, $canal, $donnees);
        $this->notificationService->notifierNouveauMessage($message);

        return $message;
    }

    public function modifierMessage(User $utilisateur, Message $message, array $donnees): Message
    {
        return $this->messagerieJoueurRepository->mettreAJourMessage($message, $donnees);
    }

    public function supprimerMessage(User $utilisateur, Message $message): void
    {
        $this->messagerieJoueurRepository->supprimerMessage($message);
    }
}
