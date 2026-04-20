<?php

namespace App\Http\Controllers\Api\President\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\President\Notification\NotificationPresidentCollection;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationPresidentController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function index(): NotificationPresidentCollection
    {
        return new NotificationPresidentCollection(
            $this->notificationService->listerPourUtilisateur(request()->user())
        );
    }

    public function marquerCommeLue(Notification $notification): JsonResponse
    {
        abort_if((int) $notification->utilisateur_id !== (int) request()->user()->id, 403, 'Cette notification ne vous appartient pas.');

        $notification = $this->notificationService->marquerCommeLue($notification);

        return response()->json([
            'status' => true,
            'message' => 'Notification marquee comme lue avec succes.',
            'data' => ['notification' => $notification],
        ]);
    }

    public function marquerToutesCommeLues(): JsonResponse
    {
        $total = $this->notificationService->marquerToutesCommeLues(request()->user());

        return response()->json([
            'status' => true,
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
            'data' => ['notifications_mises_a_jour_total' => $total],
        ]);
    }
}
