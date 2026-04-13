<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentJoueurCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des documents du joueur recuperee avec succes.',
            'data' => [
                'documents' => $this->collection->map(function ($document) {
                    return [
                        'id' => $document->id,
                        'nom' => $document->nom,
                        'type_document' => $document->type_document,
                        'fichier' => $document->fichier,
                        'fichier_url' => $document->fichier ? asset('storage/'.$document->fichier) : null,
                        'date_ajout' => $document->date_ajout,
                    ];
                })->values(),
            ],
        ];
    }
}
