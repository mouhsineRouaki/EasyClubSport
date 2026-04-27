<?php

namespace App\Repositories\Coach\Dashboard;

use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardCoachRepository
{
    public function recupererEquipeActiveCoach(User $utilisateur): ?Equipe
    {
        return Equipe::query()
            ->where('coach_id', $utilisateur->id)
            ->with(['club'])
            ->withCount([
                'membreEquipes as joueurs_total' => function ($query) {
                    $query->where('role_equipe', 'joueur');
                },
                'evenements as evenements_total',
            ])
            ->latest()
            ->first();
    }

    public function prochainEvenement(User $utilisateur): ?Evenement
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return null;
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->where('date_debut', '>=', now())
            ->where('statut', '!=', 'annule')
            ->with(['equipe.club', 'adversaireEquipe.club'])
            ->orderBy('date_debut')
            ->first();
    }

    public function compterEquipes(User $utilisateur): int
    {
        return $this->recupererEquipeActiveCoach($utilisateur) ? 1 : 0;
    }

    public function compterJoueurs(User $utilisateur): int
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return 0;
        }

        return MembreEquipe::query()
            ->where('role_equipe', 'joueur')
            ->where('equipe_id', $equipe->id)
            ->count();
    }

    public function compterEvenementsAVenir(User $utilisateur): int
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return 0;
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->where('date_debut', '>=', now())
            ->where('statut', '!=', 'annule')
            ->count();
    }

    public function compterConvocationsEnAttente(User $utilisateur): int
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return 0;
        }

        return Convocation::query()
            ->where('statut', 'en_attente')
            ->whereHas('evenement', function ($query) use ($equipe) {
                $query->where('equipe_id', $equipe->id);
            })
            ->count();
    }

    public function listerEvenementsRecents(User $utilisateur, int $limite = 3): Collection
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->with(['equipe.club', 'adversaireEquipe.club'])
            ->orderByDesc('date_debut')
            ->limit($limite)
            ->get();
    }

    public function listerCanauxRecents(User $utilisateur, int $limite = 3): Collection
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Canal::query()
            ->where('equipe_id', $equipe->id)
            ->with('equipe.club')
            ->latest()
            ->limit($limite)
            ->get();
    }
}
