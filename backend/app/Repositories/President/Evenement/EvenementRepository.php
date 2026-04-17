<?php

namespace App\Repositories\President\Evenement;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EvenementRepository
{
    public function listerParPresident(User $utilisateur, array $filtres = []): LengthAwarePaginator
    {
        $query = Evenement::with(['equipe.club', 'adversaireEquipe.club'])
            ->whereHas('equipe.club', function ($query) use ($utilisateur) {
                $query->where('president_id', $utilisateur->id);
            });

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('titre', 'like', "%{$terme}%")
                    ->orWhere('type', 'like', "%{$terme}%")
                    ->orWhere('lieu', 'like', "%{$terme}%")
                    ->orWhere('adversaire', 'like', "%{$terme}%")
                    ->orWhereHas('adversaireEquipe', function ($adversaireQuery) use ($terme) {
                        $adversaireQuery->where('nom', 'like', "%{$terme}%");
                    });
            });
        }

        if (! empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (! empty($filtres['type'])) {
            $query->where('type', $filtres['type']);
        }

        if (! empty($filtres['date_debut'])) {
            $query->whereDate('date_debut', '>=', $filtres['date_debut']);
        }

        if (! empty($filtres['date_fin'])) {
            $query->whereDate('date_debut', '<=', $filtres['date_fin']);
        }

        return $query
            ->orderBy('date_debut')
            ->paginate(
                (int) ($filtres['per_page'] ?? 12),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function listerParEquipe(Equipe $equipe, array $filtres = []): LengthAwarePaginator
    {
        $query = Evenement::with(['equipe.club', 'adversaireEquipe.club'])
            ->where('equipe_id', $equipe->id);

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('titre', 'like', "%{$terme}%")
                    ->orWhere('type', 'like', "%{$terme}%")
                    ->orWhere('lieu', 'like', "%{$terme}%")
                    ->orWhere('adversaire', 'like', "%{$terme}%")
                    ->orWhereHas('adversaireEquipe', function ($adversaireQuery) use ($terme) {
                        $adversaireQuery->where('nom', 'like', "%{$terme}%");
                    });
            });
        }

        if (! empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (! empty($filtres['type'])) {
            $query->where('type', $filtres['type']);
        }

        if (! empty($filtres['date_debut'])) {
            $query->whereDate('date_debut', '>=', $filtres['date_debut']);
        }

        if (! empty($filtres['date_fin'])) {
            $query->whereDate('date_debut', '<=', $filtres['date_fin']);
        }

        return $query
            ->orderBy('date_debut')
            ->paginate(
                (int) ($filtres['per_page'] ?? 12),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function creer(array $donnees): Evenement
    {
        return Evenement::create($donnees)->fresh(['equipe.club', 'equipe.coach', 'adversaireEquipe.club', 'adversaireEquipe.coach', 'createur']);
    }

    public function mettreAJour(Evenement $evenement, array $donnees): Evenement
    {
        $evenement->update($donnees);

        return $evenement->fresh(['equipe.club', 'equipe.coach', 'adversaireEquipe.club', 'adversaireEquipe.coach', 'createur']);
    }

    public function supprimer(Evenement $evenement): void
    {
        $evenement->delete();
    }
}

