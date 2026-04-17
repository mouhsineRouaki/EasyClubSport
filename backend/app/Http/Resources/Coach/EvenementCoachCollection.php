<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EvenementCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des evenements du coach recuperee avec succes.',
            'data' => [
                'evenements' => $this->collection->map(function ($evenement) {
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
                        'statut_invitation_adversaire' => $evenement->statut_invitation_adversaire,
                        'adversaire_equipe_id' => $evenement->adversaire_equipe_id,
                        'adversaire_equipe' => $evenement->adversaireEquipe ? [
                            'id' => $evenement->adversaireEquipe->id,
                            'nom' => $evenement->adversaireEquipe->nom,
                            'logo_url' => $evenement->adversaireEquipe->logo ? asset('storage/'.$evenement->adversaireEquipe->logo) : null,
                        ] : null,
                        'equipe' => $evenement->equipe ? [
                            'id' => $evenement->equipe->id,
                            'nom' => $evenement->equipe->nom,
                        ] : null,
                        'club' => $evenement->equipe?->club ? [
                            'id' => $evenement->equipe->club->id,
                            'nom' => $evenement->equipe->club->nom,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}
