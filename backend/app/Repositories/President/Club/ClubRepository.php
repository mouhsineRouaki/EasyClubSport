<?php

namespace App\Repositories\President\Club;

use App\Models\Club;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClubRepository
{
    public function listerParPresident(User $utilisateur, array $filtres = []): LengthAwarePaginator
    {
        $query = Club::where('president_id', $utilisateur->id);

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('ville', 'like', "%{$terme}%")
                    ->orWhere('pays', 'like', "%{$terme}%")
                    ->orWhere('email', 'like', "%{$terme}%");
            });
        }

        return $query
            ->with([
                'president',
                'equipes' => function ($equipeQuery) {
                    $equipeQuery
                        ->with(['coach', 'membreEquipes.utilisateur', 'evenements.adversaireEquipe.club'])
                        ->withCount('membreEquipes')
                        ->latest();
                },
            ])
            ->withCount('equipes')
            ->latest()
            ->paginate(
                (int) ($filtres['per_page'] ?? 12),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function creer(array $donnees): Club
    {
        return Club::create($donnees);
    }

    public function mettreAJour(Club $club, array $donnees): Club
    {
        $club->update($donnees);

        return $club->fresh();
    }

    public function supprimer(Club $club): void
    {
        $club->delete();
    }
}

