<?php

namespace App\Repositories\Joueur\Convocation;

use App\Models\Convocation;
use App\Models\User;

class ConvocationJoueurRepository
{
    public function listerConvocations(User $utilisateur)
    {
        return Convocation::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with([
                'evenement.equipe.club',
                'evenement.adversaireEquipe.club',
            ])
            ->orderByDesc('date_convocation')
            ->orderByDesc('id')
            ->get();
    }

    public function mettreAJourConvocation(Convocation $convocation, array $donnees): Convocation
    {
        $convocation->update([
            'statut' => $donnees['statut'],
            'date_confirmation' => in_array($donnees['statut'], ['confirme', 'refuse'], true) ? now() : null,
        ]);

        return $convocation->fresh([
            'evenement.equipe.club',
            'evenement.adversaireEquipe.club',
        ]);
    }

    public function compterConvocationsEnAttente(User $utilisateur): int
    {
        return Convocation::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('statut', 'en_attente')
            ->count();
    }

    public function listerConvocationsRecentes(User $utilisateur, int $limite = 4)
    {
        return Convocation::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with([
                'evenement.equipe.club',
                'evenement.adversaireEquipe.club',
            ])
            ->orderByDesc('date_convocation')
            ->orderByDesc('id')
            ->limit($limite)
            ->get();
    }
}
