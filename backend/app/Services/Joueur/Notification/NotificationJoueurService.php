<?php

namespace App\Services\Joueur\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;

class NotificationJoueurService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function listerNotifications(User $utilisateur)
    {
        return $this->notificationService->listerPourUtilisateur($utilisateur);
    }

    public function marquerNotificationCommeLue(User $utilisateur, Notification $notification): Notification
    {
        if ((int) $notification->utilisateur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Cette notification ne vous appartient pas.');
        }

        return $this->notificationService->marquerCommeLue($notification);
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return $this->notificationService->marquerToutesCommeLues($utilisateur);
    }
}
