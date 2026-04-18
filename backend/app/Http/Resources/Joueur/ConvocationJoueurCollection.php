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
                            'date_fin' => $convocation->evenement->date_fin,
                            'lieu' => $convocation->evenement->lieu,
                            'adversaire' => $convocation->evenement->adversaire,
                            'description' => $convocation->evenement->description,
                            'statut' => $convocation->evenement->statut,
                            'adversaire_equipe' => $convocation->evenement->adversaireEquipe ? [
                                'id' => $convocation->evenement->adversaireEquipe->id,
                                'nom' => $convocation->evenement->adversaireEquipe->nom,
                                'logo_url' => $convocation->evenement->adversaireEquipe->logo ? asset('storage/'.$convocation->evenement->adversaireEquipe->logo) : null,
                                'club' => $convocation->evenement->adversaireEquipe->club ? [
                                    'id' => $convocation->evenement->adversaireEquipe->club->id,
                                    'nom' => $convocation->evenement->adversaireEquipe->club->nom,
                                    'logo_url' => $convocation->evenement->adversaireEquipe->club->logo ? asset('storage/'.$convocation->evenement->adversaireEquipe->club->logo) : null,
                                ] : null,
                            ] : null,
                        ] : null,
                        'equipe' => $convocation->evenement?->equipe ? [
                            'id' => $convocation->evenement->equipe->id,
                            'nom' => $convocation->evenement->equipe->nom,
                            'logo_url' => $convocation->evenement->equipe->logo ? asset('storage/'.$convocation->evenement->equipe->logo) : null,
                        ] : null,
                        'club' => $convocation->evenement?->equipe?->club ? [
                            'id' => $convocation->evenement->equipe->club->id,
                            'nom' => $convocation->evenement->equipe->club->nom,
                            'logo_url' => $convocation->evenement->equipe->club->logo ? asset('storage/'.$convocation->evenement->equipe->club->logo) : null,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}
