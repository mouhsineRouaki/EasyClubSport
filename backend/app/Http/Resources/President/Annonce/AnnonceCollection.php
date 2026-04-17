<?php

namespace App\Http\Resources\President\Annonce;

use App\Http\Resources\Concerns\WithPaginationMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AnnonceCollection extends ResourceCollection
{
    use WithPaginationMeta;

    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des annonces recuperee avec succes.',
            'data' => [
                'annonces' => $this->collection->map(function ($annonce) {
                    return [
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
                    ];
                })->values(),
                'pagination' => $this->paginationMeta(),
            ],
        ];
    }
}
