<?php

namespace App\Http\Controllers\Api\Coach\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Coach\NotificationCoachCollection;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Models\Notification;
use App\Services\Coach\Notification\NotificationCoachService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class NotificationCoachController extends Controller
{
    public function __construct(
        protected NotificationCoachService $notificationCoachService
    ) {
    }

    public function index(): NotificationCoachCollection
    {
        return new NotificationCoachCollection(
            $this->notificationCoachService->listerNotifications(request()->user())
        );
    }

    public function marquerCommeLue(Notification $notification): ApiResponseResource|JsonResponse
    {
        try {
            $notification = $this->notificationCoachService->marquerNotificationCommeLue(request()->user(), $notification);

            return new ApiResponseResource([
                'message' => 'Notification marquee comme lue avec succes.',
                'data' => ['notification' => $notification],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function marquerToutesCommeLues(): ApiResponseResource
    {
        $total = $this->notificationCoachService->marquerToutesNotificationsCommeLues(request()->user());

        return new ApiResponseResource([
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
            'data' => ['notifications_mises_a_jour_total' => $total],
        ]);
    }
}
