<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des messages du coach recuperee avec succes.',
            'data' => [
                'messages' => $this->collection->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'canal_id' => $message->canal_id,
                        'equipe_id' => $message->equipe_id,
                        'expediteur_id' => $message->expediteur_id,
                        'contenu' => $message->contenu,
                        'type_message' => $message->type_message,
                        'expediteur' => $message->expediteur ? [
                            'id' => $message->expediteur->id,
                            'nom' => trim(($message->expediteur->prenom ?? '').' '.($message->expediteur->nom ?? '')),
                            'email' => $message->expediteur->email,
                        ] : null,
                        'created_at' => $message->created_at,
                        'updated_at' => $message->updated_at,
                    ];
                })->values(),
            ],
        ];
    }
}
