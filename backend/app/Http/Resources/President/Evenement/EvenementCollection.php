<?php

namespace App\Http\Resources\President\Evenement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EvenementCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des evenements recuperee avec succes.',
            'data' => [
                'evenements' => $this->collection->map(function ($evenement) {
                    return [
                        'id' => $evenement->id,
                        'equipe_id' => $evenement->equipe_id,
                        'createur_id' => $evenement->createur_id,
                        'titre' => $evenement->titre,
                        'type' => $evenement->type,
                        'date_debut' => $evenement->date_debut,
                        'date_fin' => $evenement->date_fin,
                        'lieu' => $evenement->lieu,
                        'adversaire' => $evenement->adversaire,
                        'description' => $evenement->description,
                        'statut' => $evenement->statut,
                        'equipe' => $evenement->equipe ? [
                            'id' => $evenement->equipe->id,
                            'nom' => $evenement->equipe->nom,
                        ] : null,
                        'club' => $evenement->equipe?->club ? [
                            'id' => $evenement->equipe->club->id,
                            'nom' => $evenement->equipe->club->nom,
                        ] : null,
                        'created_at' => $evenement->created_at,
                        'updated_at' => $evenement->updated_at,
                    ];
                })->values(),
            ],
        ];
    }
}

