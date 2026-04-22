<?php

namespace App\Repositories\Coach\Convocation;

use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;

class ConvocationCoachRepository
{
    public function listerConvocationsEquipe(Equipe $equipe)
    {
        return Convocation::query()
            ->whereHas('evenement', function ($query) use ($equipe) {
                $query->where('equipe_id', $equipe->id);
            })
            ->with(['evenement', 'utilisateur'])
            ->orderByDesc('date_convocation')
            ->orderByDesc('id')
            ->get();
    }

    public function creerOuMettreAJourConvocation(Evenement $evenement, int $utilisateurId, string $statut): Convocation
    {
        return Convocation::updateOrCreate(
            [
                'evenement_id' => $evenement->id,
                'utilisateur_id' => $utilisateurId,
            ],
            [
                'statut' => $statut,
                'date_convocation' => now(),
                'date_confirmation' => $statut === 'confirme' ? now() : null,
            ]
        );
    }

    public function mettreAJourConvocation(Convocation $convocation, array $donnees): Convocation
    {
        $convocation->update([
            'statut' => $donnees['statut'],
            'date_confirmation' => $donnees['statut'] === 'confirme' ? now() : ($donnees['statut'] === 'refuse' ? now() : null),
        ]);

        return $convocation->fresh(['evenement.equipe.club', 'utilisateur']);
    }
}
