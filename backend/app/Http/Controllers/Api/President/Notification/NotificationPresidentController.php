<?php

namespace App\Http\Controllers\Api\President\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\President\Notification\NotificationPresidentCollection;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationPresidentController extends Controller
{
    public function index(): NotificationPresidentCollection
    {
        $notifications = Notification::query()
            ->where('utilisateur_id', request()->user()->id)
            ->with(['evenement.equipe.club', 'evenement.adversaireEquipe.club'])
            ->latest()
            ->get();

        return new NotificationPresidentCollection($notifications);
    }

    public function marquerCommeLue(Notification $notification): JsonResponse
    {
        abort_if((int) $notification->utilisateur_id !== (int) request()->user()->id, 403, 'Cette notification ne vous appartient pas.');

        $notification->update([
            'est_lue' => true,
            'date_lecture' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification marquee comme lue avec succes.',
            'data' => ['notification' => $notification->fresh(['evenement.equipe.club', 'evenement.adversaireEquipe.club'])],
        ]);
    }

    public function marquerToutesCommeLues(): JsonResponse
    {
        $total = Notification::query()
            ->where('utilisateur_id', request()->user()->id)
            ->where('est_lue', false)
            ->update([
                'est_lue' => true,
                'date_lecture' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
            'data' => ['notifications_mises_a_jour_total' => $total],
        ]);
    }
}
