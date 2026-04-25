<?php

namespace App\Repositories\President\Equipe;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;
use App\Models\Canal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EquipeRepository
{
    public function listerParClub(Club $club, array $filtres = []): LengthAwarePaginator
    {
        $query = Equipe::with('coach')
            ->where('club_id', $club->id)
            ->withCount('membreEquipes');

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('categorie', 'like', "%{$terme}%")
                    ->orWhere('description', 'like', "%{$terme}%")
                    ->orWhere('code_invitation', 'like', "%{$terme}%");
            });
        }

        if (! empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        return $query
            ->latest()
            ->paginate(
                (int) ($filtres['per_page'] ?? 12),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function listerAdversaires(array $filtres = []): LengthAwarePaginator
    {
        $query = Equipe::query()
            ->with(['club', 'coach'])
            ->withCount('membreEquipes');

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('categorie', 'like', "%{$terme}%")
                    ->orWhereHas('club', function ($clubQuery) use ($terme) {
                        $clubQuery->where('nom', 'like', "%{$terme}%")
                            ->orWhere('ville', 'like', "%{$terme}%");
                    });
            });
        }

        if (! empty($filtres['exclude_equipe_id'])) {
            $query->whereKeyNot((int) $filtres['exclude_equipe_id']);
        }

        return $query
            ->latest()
            ->paginate(
                (int) ($filtres['per_page'] ?? 20),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
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
        if ($equipe->coach_id && (int) $equipe->coach_id !== (int) $coach->id) {
            MembreEquipe::query()
                ->where('equipe_id', $equipe->id)
                ->where('utilisateur_id', $equipe->coach_id)
                ->where('role_equipe', 'coach')
                ->delete();
        }

        $equipe->update([
            'coach_id' => $coach->id,
        ]);

        MembreEquipe::query()->updateOrCreate(
            [
                'equipe_id' => $equipe->id,
                'utilisateur_id' => $coach->id,
            ],
            [
                'role_equipe' => 'coach',
                'date_affectation' => now()->toDateString(),
            ]
        );

        Canal::query()
            ->where('equipe_id', $equipe->id)
            ->get()
            ->each(fn (Canal $canal) => $canal->utilisateurs()->syncWithoutDetaching([$coach->id]));

        return $equipe->fresh(['coach']);
    }

    public function retirerCoach(Equipe $equipe): Equipe
    {
        if ($equipe->coach_id) {
            MembreEquipe::query()
                ->where('equipe_id', $equipe->id)
                ->where('utilisateur_id', $equipe->coach_id)
                ->where('role_equipe', 'coach')
                ->delete();
        }

        $equipe->update([
            'coach_id' => null,
        ]);

        return $equipe->fresh(['coach']);
    }

    public function listerJoueurs(Equipe $equipe, array $filtres = []): LengthAwarePaginator
    {
        $query = $equipe->utilisateurs()
            ->wherePivot('role_equipe', 'joueur')
            ->select('users.*');

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('users.nom', 'like', "%{$terme}%")
                    ->orWhere('users.prenom', 'like', "%{$terme}%")
                    ->orWhere('users.email', 'like', "%{$terme}%")
                    ->orWhere('users.telephone', 'like', "%{$terme}%");
            });
        }

        return $query->paginate(
            (int) ($filtres['per_page'] ?? 12),
            ['*'],
            'page',
            (int) ($filtres['page'] ?? 1)
        );
    }

    public function listerJoueursDisponibles(Equipe $equipe, array $filtres = []): LengthAwarePaginator
    {
        $query = User::query()
            ->where('role', 'joueur')
            ->whereDoesntHave('membreEquipes', function ($subQuery) use ($equipe) {
                $subQuery->where('role_equipe', 'joueur')
                    ->where('equipe_id', '!=', $equipe->id);
            })
            ->whereDoesntHave('membreEquipes', function ($subQuery) use ($equipe) {
                $subQuery->where('role_equipe', 'joueur')
                    ->where('equipe_id', $equipe->id);
            });

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('prenom', 'like', "%{$terme}%")
                    ->orWhere('email', 'like', "%{$terme}%")
                    ->orWhere('telephone', 'like', "%{$terme}%");
            });
        }

        return $query
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(
                (int) ($filtres['per_page'] ?? 6),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function trouverMembreEquipe(int $utilisateurId): ?MembreEquipe
    {
        return MembreEquipe::where('utilisateur_id', $utilisateurId)
            ->where('role_equipe', 'joueur')
            ->first();
    }

    public function trouverEquipeDuCoach(int $coachId): ?Equipe
    {
        return Equipe::query()
            ->where('coach_id', $coachId)
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

        Canal::query()
            ->where('equipe_id', $equipe->id)
            ->get()
            ->each(fn (Canal $canal) => $canal->utilisateurs()->syncWithoutDetaching([$joueur->id]));
    }

    public function retirerJoueur(Equipe $equipe, User $joueur): void
    {
        MembreEquipe::where('equipe_id', $equipe->id)
            ->where('utilisateur_id', $joueur->id)
            ->where('role_equipe', 'joueur')
            ->delete();
    }
}
