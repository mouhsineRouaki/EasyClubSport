<?php

namespace App\Http\Controllers\Api\Joueur\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Http\Resources\Joueur\NotificationJoueurCollection;
use App\Models\Notification;
use App\Services\Joueur\Notification\NotificationJoueurService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class NotificationJoueurController extends Controller
{
    public function __construct(
        protected NotificationJoueurService $notificationJoueurService
    ) {
    }

    public function index(): NotificationJoueurCollection
    {
        return new NotificationJoueurCollection(
            $this->notificationJoueurService->listerNotifications(request()->user())
        );
    }

    public function marquerCommeLue(Notification $notification): ApiResponseResource|JsonResponse
    {
        try {
            $notification = $this->notificationJoueurService->marquerNotificationCommeLue(request()->user(), $notification);

            return new ApiResponseResource([
                'message' => 'Notification marquee comme lue avec succes.',
                'data' => [
                    'notification' => [
                        'id' => $notification->id,
                        'est_lue' => $notification->est_lue,
                        'date_lecture' => $notification->date_lecture,
                    ],
                ],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function marquerToutesCommeLues(): ApiResponseResource
    {
        $total = $this->notificationJoueurService->marquerToutesNotificationsCommeLues(request()->user());

        return new ApiResponseResource([
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
            'data' => ['notifications_mises_a_jour_total' => $total],
        ]);
    }
}
