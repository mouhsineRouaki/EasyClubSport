<?php

namespace App\Repositories\President;

use App\Models\Club;
use App\Models\Equipe;
use Illuminate\Database\Eloquent\Collection;

class EquipeRepository
{
    public function listerParClub(Club $club): Collection
    {
        return Equipe::where('club_id', $club->id)
            ->latest()
            ->get();
    }

    public function creer(array $donnees): Equipe
    {
        return Equipe::create($donnees);
    }

    public function mettreAJour(Equipe $equipe, array $donnees): Equipe
    {
        $equipe->update($donnees);

        return $equipe->fresh();
    }

    public function supprimer(Equipe $equipe): void
    {
        $equipe->delete();
    }

    public function codeInvitationExiste(string $codeInvitation): bool
    {
        return Equipe::where('code_invitation', $codeInvitation)->exists();
    }
}
