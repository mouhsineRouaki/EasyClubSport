<?php

namespace App\Http\Resources\President;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardPresidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => $this->resource['message'],
            'data' => [
                'statistiques' => $this->resource['statistiques'],
                'clubs_recents' => $this->resource['clubs_recents']->map(function ($club) {
                    return [
                        'id' => $club->id,
                        'nom' => $club->nom,
                        'logo' => $club->logo,
                        'logo_url' => $club->logo ? asset('storage/'.$club->logo) : null,
                        'ville' => $club->ville,
                        'equipes_count' => $club->equipes_count,
                        'created_at' => $club->created_at,
                    ];
                })->values(),
                'equipes_recentes' => $this->resource['equipes_recentes']->map(function ($equipe) {
                    return [
                        'id' => $equipe->id,
                        'club_id' => $equipe->club_id,
                        'nom' => $equipe->nom,
                        'categorie' => $equipe->categorie,
                        'logo' => $equipe->logo,
                        'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                        'statut' => $equipe->statut,
                        'joueurs_count' => $equipe->joueurs_count,
                        'club' => [
                            'id' => $equipe->club?->id,
                            'nom' => $equipe->club?->nom,
                        ],
                        'coach' => $equipe->coach ? [
                            'id' => $equipe->coach->id,
                            'nom' => $equipe->coach->nom,
                            'prenom' => $equipe->coach->prenom,
                            'email' => $equipe->coach->email,
                        ] : null,
                        'created_at' => $equipe->created_at,
                    ];
                })->values(),
                'prochains_evenements' => $this->resource['prochains_evenements']->map(function ($evenement) {
                    return [
                        'id' => $evenement->id,
                        'titre' => $evenement->titre,
                        'type' => $evenement->type,
                        'date_debut' => $evenement->date_debut,
                        'date_fin' => $evenement->date_fin,
                        'lieu' => $evenement->lieu,
                        'adversaire' => $evenement->adversaire,
                        'statut' => $evenement->statut,
                        'equipe' => [
                            'id' => $evenement->equipe?->id,
                            'nom' => $evenement->equipe?->nom,
                        ],
                        'club' => [
                            'id' => $evenement->equipe?->club?->id,
                            'nom' => $evenement->equipe?->club?->nom,
                        ],
                    ];
                })->values(),
            ],
        ];
    }
}
