<?php

namespace App\Http\Resources\President\Annonce;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $annonce = $this->resource['annonce'];

        return [
            'status' => true,
            'message' => $this->resource['message'],
            'data' => [
                'annonce' => [
                    'id' => $annonce->id,
                    'club_id' => $annonce->club_id,
                    'auteur_id' => $annonce->auteur_id,
                    'titre' => $annonce->titre,
                    'contenu' => $annonce->contenu,
                    'image' => $annonce->image,
                    'image_url' => $annonce->image ? asset('storage/'.$annonce->image) : null,
                    'est_active' => $annonce->est_active,
                    'club' => $annonce->club ? [
                        'id' => $annonce->club->id,
                        'nom' => $annonce->club->nom,
                    ] : null,
                    'auteur' => $annonce->auteur ? [
                        'id' => $annonce->auteur->id,
                        'nom' => trim(($annonce->auteur->prenom ?? '').' '.($annonce->auteur->nom ?? '')),
                        'email' => $annonce->auteur->email,
                    ] : null,
                    'created_at' => $annonce->created_at,
                    'updated_at' => $annonce->updated_at,
                ],
            ],
        ];
    }
}
