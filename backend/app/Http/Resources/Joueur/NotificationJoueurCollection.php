<?php

namespace App\Http\Resources\Joueur;

use App\Support\NotificationPresenter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationJoueurCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des notifications du joueur recuperee avec succes.',
            'data' => [
                'notifications_non_lues_total' => $this->collection->where('est_lue', false)->count(),
                'notifications' => $this->collection->map(fn ($notification) => NotificationPresenter::presenter($notification))->values(),
            ],
        ];
    }
}
