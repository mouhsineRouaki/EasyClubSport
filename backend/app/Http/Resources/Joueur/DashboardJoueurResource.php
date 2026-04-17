<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardJoueurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $equipe = $this['equipe'];
        $prochainEvenement = $this['prochain_evenement'];

        return [
            'status' => true,
            'message' => 'Dashboard joueur recupere avec succes.',
            'data' => [
                'equipe' => $equipe ? [
                    'id' => $equipe->id,
                    'nom' => $equipe->nom,
                    'categorie' => $equipe->categorie,
                    'club' => $equipe->club ? [
                        'id' => $equipe->club->id,
                        'nom' => $equipe->club->nom,
                    ] : null,
                ] : null,
                'prochain_evenement' => $prochainEvenement ? [
                    'id' => $prochainEvenement->id,
                    'titre' => $prochainEvenement->titre,
                    'type' => $prochainEvenement->type,
                    'date_debut' => $prochainEvenement->date_debut,
                    'lieu' => $prochainEvenement->lieu,
                    'adversaire' => $prochainEvenement->adversaire,
                ] : null,
                'statistiques' => [
                    'convocations_en_attente_total' => $this['convocations_en_attente_total'],
                    'notifications_non_lues_total' => $this['notifications_non_lues_total'],
                ],
                'dernieres_annonces' => collect($this['dernieres_annonces'])->map(fn ($annonce) => [
                    'id' => $annonce->id,
                    'titre' => $annonce->titre,
                    'contenu' => $annonce->contenu,
                    'image' => $annonce->image,
                    'image_url' => $annonce->image ? asset('storage/'.$annonce->image) : null,
                    'created_at' => $annonce->created_at,
                ])->values(),
                'derniers_documents' => collect($this['derniers_documents'])->map(fn ($document) => [
                    'id' => $document->id,
                    'nom' => $document->nom,
                    'type_document' => $document->type_document,
                    'date_ajout' => $document->date_ajout,
                ])->values(),
                'derniers_canaux' => collect($this['derniers_canaux'])->map(fn ($canal) => [
                    'id' => $canal->id,
                    'nom' => $canal->nom,
                    'type_canal' => $canal->type_canal,
                ])->values(),
            ],
        ];
    }
}
