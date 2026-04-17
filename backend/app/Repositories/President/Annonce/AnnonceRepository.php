<?php

namespace App\Repositories\President\Annonce;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnonceRepository
{
    public function listerParPresident(User $utilisateur, array $filtres = []): LengthAwarePaginator
    {
        $query = Annonce::with(['club', 'auteur'])
            ->whereHas('club', function ($query) use ($utilisateur) {
                $query->where('president_id', $utilisateur->id);
            });

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('titre', 'like', "%{$terme}%")
                    ->orWhere('contenu', 'like', "%{$terme}%");
            });
        }

        if (isset($filtres['est_active']) && $filtres['est_active'] !== '') {
            $query->where('est_active', (bool) $filtres['est_active']);
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

    public function listerParClub(Club $club, array $filtres = []): LengthAwarePaginator
    {
        $query = Annonce::with(['club', 'auteur'])
            ->where('club_id', $club->id);

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('titre', 'like', "%{$terme}%")
                    ->orWhere('contenu', 'like', "%{$terme}%");
            });
        }

        if (isset($filtres['est_active']) && $filtres['est_active'] !== '') {
            $query->where('est_active', (bool) $filtres['est_active']);
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

    public function creer(array $donnees): Annonce
    {
        return Annonce::create($donnees)->load(['club', 'auteur']);
    }

    public function mettreAJour(Annonce $annonce, array $donnees): Annonce
    {
        $annonce->update($donnees);

        return $annonce->fresh(['club', 'auteur']);
    }

    public function supprimer(Annonce $annonce): void
    {
        $annonce->delete();
    }
}
