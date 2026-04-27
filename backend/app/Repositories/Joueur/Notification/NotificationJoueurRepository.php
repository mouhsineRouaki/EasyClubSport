<?php

namespace App\Repositories\Joueur\Notification;

use App\Models\Notification;
use App\Models\User;

class NotificationJoueurRepository
{
    public function listerNotifications(User $utilisateur)
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function marquerNotificationCommeLue(Notification $notification): Notification
    {
        $notification->update([
            'est_lue' => true,
            'date_lecture' => now(),
        ]);

        return $notification->fresh();
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('est_lue', false)
            ->update([
                'est_lue' => true,
                'date_lecture' => now(),
            ]);
    }

    public function compterNotificationsNonLues(User $utilisateur): int
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('est_lue', false)
            ->count();
    }
}
