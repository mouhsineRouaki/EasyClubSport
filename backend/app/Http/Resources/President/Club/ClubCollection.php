<?php

namespace App\Http\Resources\President\Club;

use App\Http\Resources\Concerns\WithPaginationMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClubCollection extends ResourceCollection
{
    use WithPaginationMeta;

    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des clubs recuperee avec succes.',
            'data' => [
                'clubs' => $this->collection->map(function ($club) {
                    $equipes = $club->equipes ?? collect();
                    $joueursTotal = $equipes->sum('membre_equipes_count');
                    $coachs = $equipes
                        ->pluck('coach')
                        ->filter()
                        ->unique('id')
                        ->values();

                    return [
                        'id' => $club->id,
                        'president_id' => $club->president_id,
                        'nom' => $club->nom,
                        'logo' => $club->logo,
                        'logo_url' => $club->logo ? asset('storage/'.$club->logo) : null,
                        'adresse' => $club->adresse,
                        'telephone' => $club->telephone,
                        'email' => $club->email,
                        'description' => $club->description,
                        'ville' => $club->ville,
                        'pays' => $club->pays,
                        'president' => $club->president ? [
                            'id' => $club->president->id,
                            'nom' => trim(($club->president->prenom ?? '').' '.($club->president->nom ?? '')),
                            'email' => $club->president->email,
                            'telephone' => $club->president->telephone,
                            'photo' => $club->president->photo,
                            'photo_url' => $club->president->photo ? asset('storage/'.$club->president->photo) : null,
                        ] : null,
                        'equipes_total' => $club->equipes_count ?? $equipes->count(),
                        'joueurs_total' => $joueursTotal,
                        'coachs_total' => $coachs->count(),
                        'coach_principal' => $coachs->first() ? [
                            'id' => $coachs->first()->id,
                            'nom' => trim(($coachs->first()->prenom ?? '').' '.($coachs->first()->nom ?? '')),
                            'email' => $coachs->first()->email,
                            'photo' => $coachs->first()->photo,
                            'photo_url' => $coachs->first()->photo ? asset('storage/'.$coachs->first()->photo) : null,
                        ] : null,
                        'equipes' => $equipes->take(4)->map(function ($equipe) {
                            return [
                                'id' => $equipe->id,
                                'nom' => $equipe->nom,
                                'categorie' => $equipe->categorie,
                                'logo' => $equipe->logo,
                                'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                                'joueurs_total' => $equipe->membre_equipes_count ?? 0,
                                'coach' => $equipe->coach ? [
                                    'id' => $equipe->coach->id,
                                    'nom' => trim(($equipe->coach->prenom ?? '').' '.($equipe->coach->nom ?? '')),
                                    'email' => $equipe->coach->email,
                                    'photo' => $equipe->coach->photo,
                                    'photo_url' => $equipe->coach->photo ? asset('storage/'.$equipe->coach->photo) : null,
                                ] : null,
                                'joueurs' => ($equipe->membreEquipes ?? collect())->map(function ($membreEquipe) {
                                    $joueur = $membreEquipe->utilisateur;

                                    return $joueur ? [
                                        'id' => $joueur->id,
                                        'nom' => trim(($joueur->prenom ?? '').' '.($joueur->nom ?? '')),
                                        'email' => $joueur->email,
                                        'telephone' => $joueur->telephone,
                                        'photo' => $joueur->photo,
                                        'photo_url' => $joueur->photo ? asset('storage/'.$joueur->photo) : null,
                                        'role_equipe' => $membreEquipe->role_equipe,
                                        'date_affectation' => $membreEquipe->date_affectation,
                                    ] : null;
                                })->filter()->values(),
                                'evenements_passes' => ($equipe->evenements ?? collect())
                                    ->filter(fn ($evenement) => $evenement->date_debut && $evenement->date_debut->isPast())
                                    ->sortByDesc('date_debut')
                                    ->take(5)
                                    ->map(function ($evenement) {
                                        return [
                                            'id' => $evenement->id,
                                            'titre' => $evenement->titre,
                                            'type' => $evenement->type,
                                            'date_debut' => $evenement->date_debut,
                                            'date_fin' => $evenement->date_fin,
                                            'lieu' => $evenement->lieu,
                                            'adversaire' => $evenement->adversaire,
                                            'adversaire_equipe_id' => $evenement->adversaire_equipe_id,
                                            'adversaire_equipe' => $evenement->adversaireEquipe ? [
                                                'id' => $evenement->adversaireEquipe->id,
                                                'nom' => $evenement->adversaireEquipe->nom,
                                                'categorie' => $evenement->adversaireEquipe->categorie,
                                                'logo' => $evenement->adversaireEquipe->logo,
                                                'logo_url' => $evenement->adversaireEquipe->logo ? asset('storage/'.$evenement->adversaireEquipe->logo) : null,
                                                'club' => $evenement->adversaireEquipe->club ? [
                                                    'id' => $evenement->adversaireEquipe->club->id,
                                                    'nom' => $evenement->adversaireEquipe->club->nom,
                                                ] : null,
                                            ] : null,
                                            'statut' => $evenement->statut,
                                            'statut_invitation_adversaire' => $evenement->statut_invitation_adversaire,
                                        ];
                                    })->values(),
                            ];
                        })->values(),
                        'created_at' => $club->created_at,
                        'updated_at' => $club->updated_at,
                    ];
                })->values(),
                'pagination' => $this->paginationMeta(),
            ],
        ];
    }
}

