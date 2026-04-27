<?php

namespace App\Repositories\Coach\Evenement;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\FeuilleMatch;
use App\Models\MembreEquipe;
use App\Models\StatistiqueMatch;

class EvenementCoachRepository
{
    public function listerEvenementsEquipe(Equipe $equipe)
    {
        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->with(['equipe.club', 'adversaireEquipe.club', 'feuilleMatch.compositions.utilisateur', 'equipe.membreEquipes.utilisateur'])
            ->orderBy('date_debut')
            ->get();
    }

    public function recupererEvenementAvecComposition(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch.compositions.utilisateur',
            'equipe.membreEquipes.utilisateur',
        ]);
    }

    public function recupererEvenementAvecFeuilleMatch(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch',
        ]);
    }

    public function recupererEvenementAvecStatistiques(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch.statistiquesMatchs.utilisateur',
        ]);
    }

    public function enregistrerCompositionMatch(Evenement $evenement, array $donnees): Evenement
    {
        $feuilleMatch = $evenement->feuilleMatch()->firstOrCreate(
            ['evenement_id' => $evenement->id],
            [
                'formation' => null,
                'notes' => null,
                'est_validee' => false,
                'score_equipe' => null,
                'score_adversaire' => null,
                'resume_match' => null,
            ]
        );

        $feuilleMatch->update([
            'formation' => $donnees['formation'] ?? null,
            'notes' => $donnees['notes'] ?? null,
            'est_validee' => (bool) ($donnees['est_validee'] ?? false),
        ]);

        $feuilleMatch->compositions()->delete();

        $lignes = collect($donnees['titulaires'] ?? [])
            ->map(fn ($joueur) => [
                'utilisateur_id' => (int) $joueur['utilisateur_id'],
                'type_placement' => 'titulaire',
                'position_joueur' => $joueur['position_joueur'] ?? null,
            ])
            ->concat(
                collect($donnees['remplacants'] ?? [])
                    ->map(fn ($joueur) => [
                        'utilisateur_id' => (int) $joueur['utilisateur_id'],
                        'type_placement' => 'remplacant',
                        'position_joueur' => $joueur['position_joueur'] ?? null,
                    ])
            )
            ->values();

        if ($lignes->isNotEmpty()) {
            $feuilleMatch->compositions()->createMany($lignes->all());
        }

        return $this->recupererEvenementAvecComposition($evenement);
    }

    public function enregistrerFeuilleMatch(Evenement $evenement, array $donnees): Evenement
    {
        $feuilleMatch = $evenement->feuilleMatch()->firstOrCreate(
            ['evenement_id' => $evenement->id],
            [
                'formation' => null,
                'notes' => null,
                'est_validee' => false,
                'score_equipe' => null,
                'score_adversaire' => null,
                'resume_match' => null,
            ]
        );

        $feuilleMatch->update([
            'score_equipe' => $donnees['score_equipe'] ?? null,
            'score_adversaire' => $donnees['score_adversaire'] ?? null,
            'resume_match' => $donnees['resume_match'] ?? null,
        ]);

        return $this->recupererEvenementAvecFeuilleMatch($evenement);
    }

    public function enregistrerStatistiquesMatch(Evenement $evenement, array $donnees): Evenement
    {
        $feuilleMatch = $evenement->feuilleMatch()->firstOrCreate(
            ['evenement_id' => $evenement->id],
            [
                'formation' => null,
                'notes' => null,
                'est_validee' => false,
                'score_equipe' => null,
                'score_adversaire' => null,
                'resume_match' => null,
            ]
        );

        $scoreEquipe = $feuilleMatch->score_equipe ?? 0;
        $scoreAdversaire = $feuilleMatch->score_adversaire ?? 0;
        $idsConserves = collect($donnees['joueurs'] ?? [])->pluck('utilisateur_id')->map(fn ($id) => (int) $id)->all();

        foreach ($donnees['joueurs'] ?? [] as $ligne) {
            StatistiqueMatch::query()->updateOrCreate(
                [
                    'feuille_match_id' => $feuilleMatch->id,
                    'utilisateur_id' => (int) $ligne['utilisateur_id'],
                ],
                [
                    'score_equipe' => $scoreEquipe,
                    'score_adversaire' => $scoreAdversaire,
                    'buts' => (int) ($ligne['buts'] ?? 0),
                    'passes_decisives' => (int) ($ligne['passes_decisives'] ?? 0),
                    'cartons_jaunes' => (int) ($ligne['cartons_jaunes'] ?? 0),
                    'cartons_rouges' => (int) ($ligne['cartons_rouges'] ?? 0),
                    'minutes_jouees' => (int) ($ligne['minutes_jouees'] ?? 0),
                ]
            );
        }

        $feuilleMatch->statistiquesMatchs()
            ->when($idsConserves, fn ($query) => $query->whereNotIn('utilisateur_id', $idsConserves), fn ($query) => $query)
            ->delete();

        return $this->recupererEvenementAvecStatistiques($evenement);
    }

    public function listerDisponibilitesEvenement(Equipe $equipe, Evenement $evenement)
    {
        $membres = MembreEquipe::query()
            ->where('equipe_id', $equipe->id)
            ->where('role_equipe', 'joueur')
            ->with('utilisateur')
            ->orderByDesc('date_affectation')
            ->get();

        $disponibilites = $evenement->disponibilites()
            ->whereIn('utilisateur_id', $membres->pluck('utilisateur_id'))
            ->get()
            ->keyBy('utilisateur_id');

        $convocations = $evenement->convocations()
            ->whereIn('utilisateur_id', $membres->pluck('utilisateur_id'))
            ->get()
            ->keyBy('utilisateur_id');

        return $membres->map(function ($membre) use ($disponibilites, $convocations) {
            $utilisateur = $membre->utilisateur;
            $disponibilite = $disponibilites->get($membre->utilisateur_id);
            $convocation = $convocations->get($membre->utilisateur_id);

            return [
                'membre_id' => $membre->id,
                'utilisateur_id' => $membre->utilisateur_id,
                'date_affectation' => $membre->date_affectation,
                'joueur' => $utilisateur ? [
                    'id' => $utilisateur->id,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'name' => $utilisateur->name,
                    'email' => $utilisateur->email,
                    'photo' => $utilisateur->photo,
                    'photo_url' => $utilisateur->photo ? asset('storage/'.$utilisateur->photo) : null,
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
        })->values();
    }

    public function creerEvenement(array $donnees): Evenement
    {
        return Evenement::create($donnees)->fresh(['equipe.club', 'adversaireEquipe.club', 'createur']);
    }

    public function mettreAJourEvenement(Evenement $evenement, array $donnees): Evenement
    {
        $evenement->update($donnees);

        return $evenement->fresh(['equipe.club', 'adversaireEquipe.club', 'createur']);
    }

    public function supprimerEvenement(Evenement $evenement): void
    {
        $evenement->delete();
    }
}
