<?php

namespace App\Services\Coach\Equipe;

use App\Models\Equipe;
use App\Models\User;
use App\Repositories\Coach\Equipe\EquipeCoachRepository;
use Illuminate\Auth\Access\AuthorizationException;

class EquipeCoachService
{
    public function __construct(
        protected EquipeCoachRepository $equipeCoachRepository
    ) {
    }

    public function listerEquipes(User $utilisateur)
    {
        return $this->equipeCoachRepository->listerEquipesCoach($utilisateur);
    }

    public function listerJoueursEquipe(User $utilisateur, Equipe $equipe)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->equipeCoachRepository->listerJoueursEquipe($equipe);
    }

    protected function verifierEquipeCoach(User $utilisateur, Equipe $equipe): void
    {
        if ((int) $equipe->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez gerer que vos equipes.');
        }
    }
}
