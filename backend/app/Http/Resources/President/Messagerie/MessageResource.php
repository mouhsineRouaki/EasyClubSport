<?php

namespace App\Http\Resources\President\Messagerie;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $message = $this->resource['message'];

        return [
            'status' => true,
            'message' => $this->resource['message_text'],
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'canal_id' => $message->canal_id,
                    'equipe_id' => $message->equipe_id,
                    'expediteur_id' => $message->expediteur_id,
                    'contenu' => $message->contenu,
                    'type_message' => $message->type_message,
                    'equipe' => $message->equipe ? [
                        'id' => $message->equipe->id,
                        'nom' => $message->equipe->nom,
                    ] : null,
                    'club' => $message->equipe?->club ? [
                        'id' => $message->equipe->club->id,
                        'nom' => $message->equipe->club->nom,
                    ] : null,
                    'expediteur' => $message->expediteur ? [
                        'id' => $message->expediteur->id,
                        'nom' => trim(($message->expediteur->prenom ?? '').' '.($message->expediteur->nom ?? '')),
                        'email' => $message->expediteur->email,
                    ] : null,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ],
            ],
        ];
    }
}
