<?php

namespace App\Repositories\Coach\Equipe;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    public function trouverJoueurDansEquipe(Equipe $equipe, User $joueur): ?MembreEquipe
    {
        return MembreEquipe::query()
            ->where('equipe_id', $equipe->id)
            ->where('utilisateur_id', $joueur->id)
            ->where('role_equipe', 'joueur')
            ->with('utilisateur')
            ->first();
    }

    public function creerJoueur(Equipe $equipe, array $donnees): User
    {
        return DB::transaction(function () use ($equipe, $donnees) {
            $joueur = User::create([
                ...$donnees,
                'role' => 'joueur',
                'statut' => $donnees['statut'] ?? 'actif',
                'password' => Hash::make('password'),
            ]);

            MembreEquipe::create([
                'equipe_id' => $equipe->id,
                'utilisateur_id' => $joueur->id,
                'role_equipe' => 'joueur',
                'date_affectation' => now()->toDateString(),
            ]);

            Canal::query()
                ->where('equipe_id', $equipe->id)
                ->get()
                ->each(fn (Canal $canal) => $canal->utilisateurs()->syncWithoutDetaching([$joueur->id]));

            return $joueur->fresh();
        });
    }

    public function mettreAJourJoueur(User $joueur, array $donnees): User
    {
        $joueur->update($donnees);

        return $joueur->fresh();
    }

    public function retirerJoueur(Equipe $equipe, User $joueur): void
    {
        MembreEquipe::query()
            ->where('equipe_id', $equipe->id)
            ->where('utilisateur_id', $joueur->id)
            ->where('role_equipe', 'joueur')
            ->delete();

        Canal::query()
            ->where('equipe_id', $equipe->id)
            ->get()
            ->each(fn (Canal $canal) => $canal->utilisateurs()->detach($joueur->id));
    }
}
