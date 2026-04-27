<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isPresident()
            || $utilisateur->isCoach()
            || $utilisateur->isJoueur();
    }

    public function voir(User $utilisateur, Notification $notification): bool
    {
        return $utilisateur->ownsNotification($notification);
    }

    public function modifier(User $utilisateur, Notification $notification): bool
    {
        return $this->voir($utilisateur, $notification);
    }
}
