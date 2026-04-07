<?php

namespace App\Repositories\President\Annonce;

use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AnnonceRepository
{
    public function listerParPresident(User $utilisateur): Collection
    {
        return Annonce::with(['club', 'auteur'])
            ->whereHas('club', function ($query) use ($utilisateur) {
                $query->where('president_id', $utilisateur->id);
            })
            ->latest()
            ->get();
    }

    public function listerParClub(Club $club): Collection
    {
        return Annonce::with(['club', 'auteur'])
            ->where('club_id', $club->id)
            ->latest()
            ->get();
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
