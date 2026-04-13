<?php

namespace App\Http\Resources\Joueur;

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
                'notifications' => $this->collection->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'titre' => $notification->titre,
                        'contenu' => $notification->contenu,
                        'type_notification' => $notification->type_notification,
                        'est_lue' => $notification->est_lue,
                        'date_lecture' => $notification->date_lecture,
                        'created_at' => $notification->created_at,
                    ];
                })->values(),
            ],
        ];
    }
}
