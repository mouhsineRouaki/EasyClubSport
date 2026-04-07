<?php

namespace App\Repositories\President\Evenement;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EvenementRepository
{
    public function listerParPresident(User $utilisateur): Collection
    {
        return Evenement::with(['equipe.club'])
            ->whereHas('equipe.club', function ($query) use ($utilisateur) {
                $query->where('president_id', $utilisateur->id);
            })
            ->orderBy('date_debut')
            ->get();
    }

    public function listerParEquipe(Equipe $equipe): Collection
    {
        return Evenement::where('equipe_id', $equipe->id)
            ->orderBy('date_debut')
            ->get();
    }

    public function creer(array $donnees): Evenement
    {
        return Evenement::create($donnees)->fresh(['equipe.club']);
    }

    public function mettreAJour(Evenement $evenement, array $donnees): Evenement
    {
        $evenement->update($donnees);

        return $evenement->fresh(['equipe.club']);
    }

    public function supprimer(Evenement $evenement): void
    {
        $evenement->delete();
    }
}

