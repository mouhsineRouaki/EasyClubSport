<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EvenementJoueurCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des evenements du joueur recuperee avec succes.',
            'data' => [
                'evenements' => $this->collection->map(function ($evenement) {
                    $disponibilite = $evenement->relationLoaded('disponibilites') ? $evenement->disponibilites->first() : null;
                    $convocation = $evenement->relationLoaded('convocations') ? $evenement->convocations->first() : null;

                    return [
                        'id' => $evenement->id,
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
                        'disponibilite' => $disponibilite ? [
                            'id' => $disponibilite->id,
                            'reponse' => $disponibilite->reponse,
                            'commentaire' => $disponibilite->commentaire,
                            'date_reponse' => $disponibilite->date_reponse,
                        ] : null,
                        'convocation' => $convocation ? [
                            'id' => $convocation->id,
                            'statut' => $convocation->statut,
                            'date_convocation' => $convocation->date_convocation,
                            'date_confirmation' => $convocation->date_confirmation,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}
