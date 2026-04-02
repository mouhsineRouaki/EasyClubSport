<?php

namespace App\Repositories\President;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardPresidentRepository
{
    public function compterClubs(int $presidentId): int
    {
        return Club::query()
            ->where('president_id', $presidentId)
            ->count();
    }

    public function compterEquipes(int $presidentId): int
    {
        return Equipe::query()
            ->whereHas('club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->count();
    }

    public function compterJoueurs(int $presidentId): int
    {
        return User::query()
            ->where('role', 'joueur')
            ->whereHas('membreEquipes.equipe.club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->distinct('users.id')
            ->count('users.id');
    }

    public function compterCoachs(int $presidentId): int
    {
        return User::query()
            ->where('role', 'coach')
            ->whereHas('equipesCoachees.club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->distinct('users.id')
            ->count('users.id');
    }

    public function compterEvenementsAVenir(int $presidentId): int
    {
        return Evenement::query()
            ->whereHas('equipe.club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->where('date_debut', '>=', Carbon::now())
            ->count();
    }

    public function compterEvenementsPasses(int $presidentId): int
    {
        return Evenement::query()
            ->whereHas('equipe.club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->where('date_debut', '<', Carbon::now())
            ->count();
    }

    public function recupererClubsRecents(int $presidentId): Collection
    {
        return Club::query()
            ->where('president_id', $presidentId)
            ->withCount('equipes')
            ->latest()
            ->limit(3)
            ->get();
    }

    public function recupererEquipesRecentes(int $presidentId): Collection
    {
        return Equipe::query()
            ->whereHas('club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->with(['club', 'coach'])
            ->withCount([
                'membreEquipes as joueurs_count' => function ($query) {
                    $query->where('role_equipe', 'joueur');
                },
            ])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function recupererProchainsEvenements(int $presidentId): Collection
    {
        return Evenement::query()
            ->whereHas('equipe.club', function ($query) use ($presidentId) {
                $query->where('president_id', $presidentId);
            })
            ->with(['equipe.club'])
            ->where('date_debut', '>=', Carbon::now())
            ->orderBy('date_debut')
            ->limit(5)
            ->get();
    }

    public function recupererCotisations(int $presidentId): array
    {
        $resultat = DB::table('cotisations')
            ->join('clubs', 'clubs.id', '=', 'cotisations.club_id')
            ->where('clubs.president_id', $presidentId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN statut_paiement = 'paye' THEN montant ELSE 0 END), 0) as montant_paye,
                COALESCE(SUM(CASE WHEN statut_paiement != 'paye' THEN montant ELSE 0 END), 0) as montant_en_attente
            ")
            ->first();

        return [
            'montant_paye' => (float) ($resultat->montant_paye ?? 0),
            'montant_en_attente' => (float) ($resultat->montant_en_attente ?? 0),
        ];
    }
}
