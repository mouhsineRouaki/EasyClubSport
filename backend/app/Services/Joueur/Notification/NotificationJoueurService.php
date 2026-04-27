<?php

namespace App\Services\Joueur\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notification\NotificationService;
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
        return $this->notificationService->marquerCommeLue($notification);
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return $this->notificationService->marquerToutesCommeLues($utilisateur);
    }
}
