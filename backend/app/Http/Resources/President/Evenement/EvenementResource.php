<?php

namespace App\Http\Resources\President\Evenement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvenementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $evenement = $this->resource['evenement'];

        return [
            'status' => true,
            'message' => $this->resource['message'],
            'data' => [
                'evenement' => [
                    'id' => $evenement->id,
                    'equipe_id' => $evenement->equipe_id,
                    'createur_id' => $evenement->createur_id,
                    'titre' => $evenement->titre,
                    'type' => $evenement->type,
                    'date_debut' => $evenement->date_debut,
                    'date_fin' => $evenement->date_fin,
                    'lieu' => $evenement->lieu,
                    'adversaire' => $evenement->adversaire,
                    'adversaire_equipe_id' => $evenement->adversaire_equipe_id,
                    'adversaire_equipe' => $this->equipePayload($evenement->adversaireEquipe),
                    'description' => $evenement->description,
                    'statut' => $evenement->statut,
                    'statut_invitation_adversaire' => $evenement->statut_invitation_adversaire,
                    'invitation_adversaire_repondue_par' => $evenement->invitationAdversaireReponduePar ? [
                        'id' => $evenement->invitationAdversaireReponduePar->id,
                        'name' => $evenement->invitationAdversaireReponduePar->name,
                        'nom' => $evenement->invitationAdversaireReponduePar->nom,
                        'prenom' => $evenement->invitationAdversaireReponduePar->prenom,
                        'email' => $evenement->invitationAdversaireReponduePar->email,
                    ] : null,
                    'invitation_adversaire_repondue_at' => $evenement->invitation_adversaire_repondue_at,
                    'equipe' => $this->equipePayload($evenement->equipe),
                    'club' => $evenement->equipe?->club ? [
                        'id' => $evenement->equipe->club->id,
                        'nom' => $evenement->equipe->club->nom,
                        'logo' => $evenement->equipe->club->logo,
                        'logo_url' => $evenement->equipe->club->logo ? asset('storage/'.$evenement->equipe->club->logo) : null,
                    ] : null,
                    'disponibilites' => [
                        'present_total' => $evenement->disponibilites_present_total ?? 0,
                        'absent_total' => $evenement->disponibilites_absent_total ?? 0,
                        'incertain_total' => $evenement->disponibilites_incertain_total ?? 0,
                        'total_reponses' => $evenement->disponibilites_total ?? 0,
                    ],
                    'created_at' => $evenement->created_at,
                    'updated_at' => $evenement->updated_at,
                ],
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
