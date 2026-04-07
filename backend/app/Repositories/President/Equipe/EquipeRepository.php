<?php

namespace App\Repositories\President\Equipe;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EquipeRepository
{
    public function listerParClub(Club $club): Collection
    {
        return Equipe::with('coach')
            ->where('club_id', $club->id)
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

        return $equipe->fresh(['coach']);
    }

    public function supprimer(Equipe $equipe): void
    {
        $equipe->delete();
    }

    public function codeInvitationExiste(string $codeInvitation): bool
    {
        return Equipe::where('code_invitation', $codeInvitation)->exists();
    }

    public function assignerCoach(Equipe $equipe, User $coach): Equipe
    {
        $equipe->update([
            'coach_id' => $coach->id,
        ]);

        return $equipe->fresh(['coach']);
    }

    public function retirerCoach(Equipe $equipe): Equipe
    {
        $equipe->update([
            'coach_id' => null,
        ]);

        return $equipe->fresh(['coach']);
    }

    public function listerJoueurs(Equipe $equipe): Collection
    {
        return $equipe->utilisateurs()
            ->wherePivot('role_equipe', 'joueur')
            ->get();
    }

    public function trouverMembreEquipe(int $utilisateurId): ?MembreEquipe
    {
        return MembreEquipe::where('utilisateur_id', $utilisateurId)
            ->where('role_equipe', 'joueur')
            ->first();
    }

    public function joueurExisteDansEquipe(Equipe $equipe, int $utilisateurId): bool
    {
        return MembreEquipe::where('equipe_id', $equipe->id)
            ->where('utilisateur_id', $utilisateurId)
            ->where('role_equipe', 'joueur')
            ->exists();
    }

    public function ajouterJoueur(Equipe $equipe, User $joueur): void
    {
        MembreEquipe::create([
            'equipe_id' => $equipe->id,
            'utilisateur_id' => $joueur->id,
            'role_equipe' => 'joueur',
            'date_affectation' => now()->toDateString(),
        ]);
    }

    public function retirerJoueur(Equipe $equipe, User $joueur): void
    {
        MembreEquipe::where('equipe_id', $equipe->id)
            ->where('utilisateur_id', $joueur->id)
            ->where('role_equipe', 'joueur')
            ->delete();
    }
}

