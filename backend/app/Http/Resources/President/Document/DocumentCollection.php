<?php

namespace App\Http\Resources\President\Document;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des documents recuperee avec succes.',
            'data' => [
                'documents' => $this->collection->map(function ($document) {
                    return [
                        'id' => $document->id,
                        'utilisateur_id' => $document->utilisateur_id,
                        'nom' => $document->nom,
                        'type_document' => $document->type_document,
                        'fichier' => $document->fichier,
                        'fichier_url' => $document->fichier ? asset('storage/'.$document->fichier) : null,
                        'date_ajout' => $document->date_ajout,
                        'utilisateur' => $document->utilisateur ? [
                            'id' => $document->utilisateur->id,
                            'nom' => trim(($document->utilisateur->prenom ?? '').' '.($document->utilisateur->nom ?? '')),
                            'email' => $document->utilisateur->email,
                            'role' => $document->utilisateur->role,
                        ] : null,
                        'created_at' => $document->created_at,
                        'updated_at' => $document->updated_at,
                    ];
                })->values(),
            ],
        ];
    }
}
