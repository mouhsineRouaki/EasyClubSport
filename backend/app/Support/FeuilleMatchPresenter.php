<?php

namespace App\Support;

use App\Models\Evenement;

class FeuilleMatchPresenter
{
    public static function depuisEvenement(Evenement $evenement): ?array
    {
        $feuilleMatch = $evenement->feuilleMatch;

        if (! $feuilleMatch) {
            return null;
        }

        return [
            'id' => $feuilleMatch->id,
            'evenement_id' => $feuilleMatch->evenement_id,
            'formation' => $feuilleMatch->formation,
            'notes' => $feuilleMatch->notes,
            'est_validee' => (bool) $feuilleMatch->est_validee,
            'score_equipe' => $feuilleMatch->score_equipe,
            'score_adversaire' => $feuilleMatch->score_adversaire,
            'resume_match' => $feuilleMatch->resume_match,
            'updated_at' => $feuilleMatch->updated_at,
        ];
    }
}
