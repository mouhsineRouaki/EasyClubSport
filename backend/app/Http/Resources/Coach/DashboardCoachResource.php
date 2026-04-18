<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardCoachResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $equipe = $this['equipe'];
        $prochainEvenement = $this['prochain_evenement'];

        return [
            'status' => true,
            'message' => 'Dashboard coach recupere avec succes.',
            'data' => [
                'equipe' => $equipe ? [
                    'id' => $equipe->id,
                    'nom' => $equipe->nom,
                    'categorie' => $equipe->categorie,
                    'statut' => $equipe->statut,
                    'description' => $equipe->description,
                    'logo_url' => $equipe->logo_url ?? null,
                    'joueurs_total' => $equipe->joueurs_total ?? 0,
                    'evenements_total' => $equipe->evenements_total ?? 0,
                    'club' => $equipe->club ? [
                        'id' => $equipe->club->id,
                        'nom' => $equipe->club->nom,
                        'ville' => $equipe->club->ville,
                        'logo_url' => $equipe->club->logo_url ?? null,
                    ] : null,
                ] : null,
                'statistiques' => [
                    'equipes_total' => $this['equipes_total'],
                    'joueurs_total' => $this['joueurs_total'],
                    'evenements_a_venir_total' => $this['evenements_a_venir_total'],
                    'convocations_en_attente_total' => $this['convocations_en_attente_total'],
                ],
                'prochain_evenement' => $prochainEvenement ? [
                    'id' => $prochainEvenement->id,
                    'titre' => $prochainEvenement->titre,
                    'type' => $prochainEvenement->type,
                    'date_debut' => $prochainEvenement->date_debut,
                    'lieu' => $prochainEvenement->lieu,
                    'adversaire' => $prochainEvenement->adversaire,
                    'equipe' => $prochainEvenement->equipe ? [
                        'id' => $prochainEvenement->equipe->id,
                        'nom' => $prochainEvenement->equipe->nom,
                    ] : null,
                ] : null,
                'evenements_recents' => collect($this['evenements_recents'])->map(fn ($evenement) => [
                    'id' => $evenement->id,
                    'titre' => $evenement->titre,
                    'date_debut' => $evenement->date_debut,
                    'statut' => $evenement->statut,
                ])->values(),
                'canaux_recents' => collect($this['canaux_recents'])->map(fn ($canal) => [
                    'id' => $canal->id,
                    'nom' => $canal->nom,
                    'type_canal' => $canal->type_canal,
                ])->values(),
            ],
        ];
    }
}
