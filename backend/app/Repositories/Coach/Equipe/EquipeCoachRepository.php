<?php

namespace App\Repositories\Coach\Equipe;

use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;

class EquipeCoachRepository
{
    public function listerEquipesCoach(User $utilisateur)
    {
        return Equipe::query()
            ->where('coach_id', $utilisateur->id)
            ->with(['club'])
            ->withCount(['membreEquipes as joueurs_total' => function ($query) {
                $query->where('role_equipe', 'joueur');
            }])
            ->latest()
            ->get();
    }

    public function listerJoueursEquipe(Equipe $equipe)
    {
        return MembreEquipe::query()
            ->where('equipe_id', $equipe->id)
            ->where('role_equipe', 'joueur')
            ->with('utilisateur')
            ->orderByDesc('date_affectation')
            ->get();
    }
}
