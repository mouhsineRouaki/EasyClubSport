<?php

namespace App\Policies;

use App\Models\Convocation;
use App\Models\Evenement;
use App\Models\User;

class ConvocationPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isCoach() || $utilisateur->isJoueur();
    }

    public function creer(User $utilisateur, Evenement $evenement): bool
    {
        return $utilisateur->coachesEquipe($evenement->equipe);
    }

    public function voir(User $utilisateur, Convocation $convocation): bool
    {
        return $utilisateur->ownsConvocation($convocation)
            || $utilisateur->coachesEquipe($convocation->evenement?->equipe);
    }

    public function modifier(User $utilisateur, Convocation $convocation): bool
    {
        return $utilisateur->coachesEquipe($convocation->evenement?->equipe);
    }

    public function repondre(User $utilisateur, Convocation $convocation): bool
    {
        return $utilisateur->ownsConvocation($convocation);
    }
}
