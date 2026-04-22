<?php

namespace App\Services\Joueur\Messagerie;

use App\Models\Canal;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Joueur\Messagerie\MessagerieJoueurRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;

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
        $this->verifierAccesCanal($utilisateur, $canal);

        return $this->messagerieJoueurRepository->listerMessagesParCanal($canal);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $this->verifierAccesCanal($utilisateur, $canal);

        $message = $this->messagerieJoueurRepository->creerMessage($utilisateur, $canal, $donnees);
        $this->notificationService->notifierNouveauMessage($message);

        return $message;
    }

    public function modifierMessage(User $utilisateur, Message $message, array $donnees): Message
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que vos propres messages.');
        }

        return $this->messagerieJoueurRepository->mettreAJourMessage($message, $donnees);
    }

    public function supprimerMessage(User $utilisateur, Message $message): void
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez supprimer que vos propres messages.');
        }

        $this->messagerieJoueurRepository->supprimerMessage($message);
    }

    protected function verifierAccesCanal(User $utilisateur, Canal $canal): void
    {
        if (! $this->messagerieJoueurRepository->utilisateurAppartientAuCanal($utilisateur, $canal)) {
            throw new AuthorizationException('Vous n avez pas acces a ce canal.');
        }
    }
}
