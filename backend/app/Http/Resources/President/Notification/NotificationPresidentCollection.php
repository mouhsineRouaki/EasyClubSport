<?php

namespace App\Http\Resources\President\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationPresidentCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des notifications du president recuperee avec succes.',
            'data' => [
                'notifications_non_lues_total' => $this->collection->where('est_lue', false)->count(),
                'notifications' => $this->collection->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'evenement_id' => $notification->evenement_id,
                        'titre' => $notification->titre,
                        'contenu' => $notification->contenu,
                        'type_notification' => $notification->type_notification,
                        'action' => $notification->action,
                        'statut_action' => $notification->statut_action,
                        'est_lue' => $notification->est_lue,
                        'date_lecture' => $notification->date_lecture,
                        'evenement' => $this->evenementPayload($notification->evenement),
                        'created_at' => $notification->created_at,
                    ];
                })->values(),
            ],
        ];
    }

    protected function evenementPayload($evenement): ?array
    {
        if (! $evenement) {
            return null;
        }

        return [
            'id' => $evenement->id,
            'titre' => $evenement->titre,
            'type' => $evenement->type,
            'date_debut' => $evenement->date_debut,
            'statut' => $evenement->statut,
            'statut_invitation_adversaire' => $evenement->statut_invitation_adversaire,
            'equipe' => $this->equipePayload($evenement->equipe),
            'adversaire_equipe' => $this->equipePayload($evenement->adversaireEquipe),
        ];
    }

    protected function equipePayload($equipe): ?array
    {
        if (! $equipe) {
            return null;
        }

        return [
            'id' => $equipe->id,
            'nom' => $equipe->nom,
            'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
            'club' => $equipe->club ? [
                'id' => $equipe->club->id,
                'nom' => $equipe->club->nom,
                'logo_url' => $equipe->club->logo ? asset('storage/'.$equipe->club->logo) : null,
            ] : null,
        ];
    }
}
