<?php

namespace App\Repositories\President\Dashboard;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\Cotisation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DashboardPresidentRepository
{
    public function compterClubs(User $utilisateur): int
    {
        return Club::where('president_id', $utilisateur->id)->count();
    }

    public function compterEquipes(User $utilisateur): int
    {
        return Equipe::whereHas('club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })->count();
    }

    public function compterCoachs(User $utilisateur): int
    {
        return Equipe::whereHas('club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->whereNotNull('coach_id')
            ->distinct('coach_id')
            ->count('coach_id');
    }

    public function compterJoueurs(User $utilisateur): int
    {
        return MembreEquipe::whereHas('equipe.club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->distinct('utilisateur_id')
            ->count('utilisateur_id');
    }

    public function compterEvenementsAVenir(User $utilisateur): int
    {
        return Evenement::whereHas('equipe.club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->where('date_debut', '>=', now())
            ->count();
    }

    public function compterEvenementsPasses(User $utilisateur): int
    {
        return Evenement::whereHas('equipe.club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->where('date_debut', '<', now())
            ->count();
    }

    public function compterCotisationsPayees(User $utilisateur): int
    {
        return Cotisation::whereHas('club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->where('statut_paiement', 'paye')
            ->count();
    }

    public function compterCotisationsEnAttente(User $utilisateur): int
    {
        return Cotisation::whereHas('club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->where('statut_paiement', 'en_attente')
            ->count();
    }

    public function recupererClubsRecents(User $utilisateur, int $limite = 5): Collection
    {
        return Club::where('president_id', $utilisateur->id)
            ->withCount('equipes')
            ->latest()
            ->take($limite)
            ->get();
    }

    public function recupererEquipesRecentes(User $utilisateur, int $limite = 5): Collection
    {
        return Equipe::whereHas('club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->with(['club', 'coach'])
            ->withCount('membreEquipes')
            ->latest()
            ->take($limite)
            ->get();
    }

    public function recupererProchainsEvenements(User $utilisateur, int $limite = 5): Collection
    {
        return Evenement::whereHas('equipe.club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->with(['equipe.club'])
            ->where('date_debut', '>=', now())
            ->orderBy('date_debut')
            ->take($limite)
            ->get();
    }

    public function recupererDernieresAnnonces(User $utilisateur, int $limite = 5): Collection
    {
        return Annonce::whereHas('club', function ($query) use ($utilisateur) {
            $query->where('president_id', $utilisateur->id);
        })
            ->with(['club', 'auteur'])
            ->latest()
            ->take($limite)
            ->get();
    }
}

