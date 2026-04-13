<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ConvocationCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des convocations du coach recuperee avec succes.',
            'data' => [
                'convocations' => $this->collection->map(function ($convocation) {
                    return [
                        'id' => $convocation->id,
                        'statut' => $convocation->statut,
                        'date_convocation' => $convocation->date_convocation,
                        'date_confirmation' => $convocation->date_confirmation,
                        'joueur' => $convocation->utilisateur ? [
                            'id' => $convocation->utilisateur->id,
                            'nom' => trim(($convocation->utilisateur->prenom ?? '').' '.($convocation->utilisateur->nom ?? '')),
                            'email' => $convocation->utilisateur->email,
                        ] : null,
                        'evenement' => $convocation->evenement ? [
                            'id' => $convocation->evenement->id,
                            'titre' => $convocation->evenement->titre,
                            'date_debut' => $convocation->evenement->date_debut,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}