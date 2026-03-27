<?php

namespace App\Repositories\President;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ClubRepository
{
    public function listerParPresident(User $utilisateur): Collection
    {
        return Club::where('president_id', $utilisateur->id)
            ->latest()
            ->get();
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
