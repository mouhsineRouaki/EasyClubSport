<?php

namespace App\Repositories\Joueur\Equipe;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;

class EquipeJoueurRepository
{
    public function recupererEquipeActive(User $utilisateur): ?Equipe
    {
        return MembreEquipe::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('role_equipe', 'joueur')
            ->with(['equipe.club', 'equipe.coach'])
            ->orderByDesc('date_affectation')
            ->orderByDesc('id')
            ->first()?->equipe;
    }

    public function utilisateurAPourEquipeActive(User $utilisateur): bool
    {
        return MembreEquipe::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('role_equipe', 'joueur')
            ->exists();
    }

    public function trouverEquipeParCodeInvitation(string $codeInvitation): ?Equipe
    {
        return Equipe::query()
            ->where('code_invitation', $codeInvitation)
            ->with(['club', 'coach'])
            ->withCount([
                'membreEquipes as joueurs_total' => function ($query) {
                    $query->where('role_equipe', 'joueur');
                },
                'evenements as evenements_total',
            ])
            ->first();
    }

    public function rattacherJoueurAEquipe(User $utilisateur, Equipe $equipe): void
    {
        MembreEquipe::query()->create([
            'equipe_id' => $equipe->id,
            'utilisateur_id' => $utilisateur->id,
            'role_equipe' => 'joueur',
            'date_affectation' => now()->toDateString(),
        ]);

        $equipe->canaux()
            ->get()
            ->each(fn (Canal $canal) => $canal->utilisateurs()->syncWithoutDetaching([$utilisateur->id]));
    }
}
