<?php

namespace App\Http\Resources\President\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardPresidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Dashboard president recupere avec succes.',
            'data' => [
                'statistiques' => $this['statistiques'],
                'clubs_recents' => collect($this['clubs_recents'])->map(function ($club) {
                    return [
                        'id' => $club->id,
                        'nom' => $club->nom,
                        'logo' => $club->logo,
                        'logo_url' => $club->logo ? asset('storage/'.$club->logo) : null,
                        'ville' => $club->ville,
                        'equipes_total' => $club->equipes_count ?? 0,
                        'created_at' => $club->created_at,
                    ];
                })->values(),
                'equipes_recentes' => collect($this['equipes_recentes'])->map(function ($equipe) {
                    return [
                        'id' => $equipe->id,
                        'nom' => $equipe->nom,
                        'categorie' => $equipe->categorie,
                        'logo' => $equipe->logo,
                        'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                        'statut' => $equipe->statut,
                        'joueurs_total' => $equipe->membre_equipes_count ?? 0,
                        'club' => $equipe->club ? [
                            'id' => $equipe->club->id,
                            'nom' => $equipe->club->nom,
                        ] : null,
                        'coach' => $equipe->coach ? [
                            'id' => $equipe->coach->id,
                            'nom' => trim(($equipe->coach->prenom ?? '').' '.($equipe->coach->nom ?? '')),
                            'email' => $equipe->coach->email,
                        ] : null,
                        'created_at' => $equipe->created_at,
                    ];
                })->values(),
                'prochains_evenements' => collect($this['prochains_evenements'])->map(function ($evenement) {
                    return [
                        'id' => $evenement->id,
                        'titre' => $evenement->titre,
                        'type' => $evenement->type,
                        'date_debut' => $evenement->date_debut,
                        'date_fin' => $evenement->date_fin,
                        'lieu' => $evenement->lieu,
                        'adversaire' => $evenement->adversaire,
                        'adversaire_equipe_id' => $evenement->adversaire_equipe_id,
                        'adversaire_equipe' => $this->equipePayload($evenement->adversaireEquipe),
                        'statut' => $evenement->statut,
                        'statut_invitation_adversaire' => $evenement->statut_invitation_adversaire,
                        'equipe' => $this->equipePayload($evenement->equipe),
                        'club' => $evenement->equipe?->club ? [
                            'id' => $evenement->equipe->club->id,
                            'nom' => $evenement->equipe->club->nom,
                            'logo' => $evenement->equipe->club->logo,
                            'logo_url' => $evenement->equipe->club->logo ? asset('storage/'.$evenement->equipe->club->logo) : null,
                        ] : null,
                    ];
                })->values(),
                'dernieres_annonces' => collect($this['dernieres_annonces'])->map(function ($annonce) {
                    return [
                        'id' => $annonce->id,
                        'titre' => $annonce->titre,
                        'contenu' => $annonce->contenu,
                        'image' => $annonce->image,
                        'image_url' => $annonce->image ? asset('storage/'.$annonce->image) : null,
                        'est_active' => $annonce->est_active,
                        'club' => $annonce->club ? [
                            'id' => $annonce->club->id,
                            'nom' => $annonce->club->nom,
                        ] : null,
                        'auteur' => $annonce->auteur ? [
                            'id' => $annonce->auteur->id,
                            'nom' => trim(($annonce->auteur->prenom ?? '').' '.($annonce->auteur->nom ?? '')),
                            'email' => $annonce->auteur->email,
                        ] : null,
                        'created_at' => $annonce->created_at,
                    ];
                })->values(),
            ],
        ];
    }

    protected function equipePayload($equipe): ?array
    {
        if (! $equipe) {
            return null;
        }

        return [
            'id' => $equipe->id,
            'club_id' => $equipe->club_id,
            'nom' => $equipe->nom,
            'categorie' => $equipe->categorie,
            'logo' => $equipe->logo,
            'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
            'club' => $equipe->club ? [
                'id' => $equipe->club->id,
                'nom' => $equipe->club->nom,
                'logo' => $equipe->club->logo,
                'logo_url' => $equipe->club->logo ? asset('storage/'.$equipe->club->logo) : null,
            ] : null,
        ];
    }
}

