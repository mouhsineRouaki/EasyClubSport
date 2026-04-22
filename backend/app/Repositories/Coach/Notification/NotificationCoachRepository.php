<?php

namespace App\Repositories\Coach\Notification;

use App\Models\Notification;
use App\Models\User;

class NotificationCoachRepository
{
    public function listerNotifications(User $utilisateur)
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with(['evenement.equipe.club', 'evenement.adversaireEquipe.club'])
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
}
