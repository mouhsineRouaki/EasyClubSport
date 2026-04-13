<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ConvocationJoueurCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des convocations du joueur recuperee avec succes.',
            'data' => [
                'convocations' => $this->collection->map(function ($convocation) {
                    return [
                        'id' => $convocation->id,
                        'statut' => $convocation->statut,
                        'date_convocation' => $convocation->date_convocation,
                        'date_confirmation' => $convocation->date_confirmation,
                        'evenement' => $convocation->evenement ? [
                            'id' => $convocation->evenement->id,
                            'titre' => $convocation->evenement->titre,
                            'type' => $convocation->evenement->type,
                            'date_debut' => $convocation->evenement->date_debut,
                            'lieu' => $convocation->evenement->lieu,
                        ] : null,
                        'equipe' => $convocation->evenement?->equipe ? [
                            'id' => $convocation->evenement->equipe->id,
                            'nom' => $convocation->evenement->equipe->nom,
                        ] : null,
                        'club' => $convocation->evenement?->equipe?->club ? [
                            'id' => $convocation->evenement->equipe->club->id,
                            'nom' => $convocation->evenement->equipe->club->nom,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}
