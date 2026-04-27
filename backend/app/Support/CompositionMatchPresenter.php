<?php

namespace App\Support;

use App\Models\Evenement;
use App\Models\User;

class CompositionMatchPresenter
{
    public static function depuisEvenement(Evenement $evenement): ?array
    {
        if ($evenement->type !== 'match') {
            return null;
        }

        $evenement->loadMissing([
            'feuilleMatch.compositions.utilisateur',
            'equipe.membreEquipes.utilisateur',
            'equipe.club',
            'adversaireEquipe.club',
        ]);

        $feuilleMatch = $evenement->feuilleMatch;
        $compositions = $feuilleMatch?->compositions ?? collect();

        $titulaires = $compositions
            ->where('type_placement', 'titulaire')
            ->map(fn ($composition) => self::joueurPayload($composition->utilisateur, $composition->position_joueur))
            ->filter()
            ->values();

        $remplacants = $compositions
            ->where('type_placement', 'remplacant')
            ->map(fn ($composition) => self::joueurPayload($composition->utilisateur, $composition->position_joueur))
            ->filter()
            ->values();

        $joueursSelectionnesIds = $compositions
            ->pluck('utilisateur_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $absents = $evenement->equipe?->membreEquipes
            ?->where('role_equipe', 'joueur')
            ->reject(fn ($membre) => in_array((int) $membre->utilisateur_id, $joueursSelectionnesIds, true))
            ->map(fn ($membre) => self::joueurPayload($membre->utilisateur))
            ->filter()
            ->values() ?? collect();

        return [
            'feuille_match_id' => $feuilleMatch?->id,
            'formation' => $feuilleMatch?->formation,
            'notes' => $feuilleMatch?->notes,
            'est_validee' => (bool) ($feuilleMatch?->est_validee ?? false),
            'titulaires' => $titulaires,
            'remplacants' => $remplacants,
            'absents' => $absents,
        ];
    }

    protected static function joueurPayload(?User $utilisateur, ?string $position = null): ?array
    {
        if (! $utilisateur) {
            return null;
        }

        return [
            'id' => $utilisateur->id,
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'name' => $utilisateur->name,
            'email' => $utilisateur->email,
            'photo' => $utilisateur->photo,
            'photo_url' => $utilisateur->photo ? asset('storage/'.$utilisateur->photo) : null,
            'position_joueur' => $position,
        ];
    }
}
