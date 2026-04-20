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
            })
            ->withCount([
                'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
                'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
                'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
                'disponibilites as disponibilites_total' => fn ($query) => $query,
            ]);

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
            ->where('equipe_id', $equipe->id)
            ->withCount([
                'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
                'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
                'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
                'disponibilites as disponibilites_total' => fn ($query) => $query,
            ]);

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
        return Evenement::create($donnees)->fresh(['equipe.club', 'equipe.coach', 'adversaireEquipe.club', 'adversaireEquipe.coach', 'createur'])
            ->loadCount([
                'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
                'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
                'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
                'disponibilites as disponibilites_total' => fn ($query) => $query,
            ]);
    }

    public function mettreAJour(Evenement $evenement, array $donnees): Evenement
    {
        $evenement->update($donnees);

        return $evenement->fresh(['equipe.club', 'equipe.coach', 'adversaireEquipe.club', 'adversaireEquipe.coach', 'createur'])
            ->loadCount([
                'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
                'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
                'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
                'disponibilites as disponibilites_total' => fn ($query) => $query,
            ]);
    }

    public function supprimer(Evenement $evenement): void
    {
        $evenement->delete();
    }

    public function recupererAvecComposition(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch.compositions.utilisateur',
            'equipe.membreEquipes.utilisateur',
        ])->loadCount([
            'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
            'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
            'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
            'disponibilites as disponibilites_total' => fn ($query) => $query,
        ]);
    }

    public function recupererAvecFeuilleMatch(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch',
        ])->loadCount([
            'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
            'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
            'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
            'disponibilites as disponibilites_total' => fn ($query) => $query,
        ]);
    }

    public function recupererAvecStatistiques(Evenement $evenement): Evenement
    {
        return $evenement->fresh([
            'equipe.club',
            'adversaireEquipe.club',
            'feuilleMatch.statistiquesMatchs.utilisateur',
        ])->loadCount([
            'disponibilites as disponibilites_present_total' => fn ($query) => $query->where('reponse', 'present'),
            'disponibilites as disponibilites_absent_total' => fn ($query) => $query->where('reponse', 'absent'),
            'disponibilites as disponibilites_incertain_total' => fn ($query) => $query->where('reponse', 'incertain'),
            'disponibilites as disponibilites_total' => fn ($query) => $query,
        ]);
    }
}
