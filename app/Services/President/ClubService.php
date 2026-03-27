<?php

namespace App\Services\President;

use App\Models\Club;
use App\Models\User;
use App\Repositories\President\ClubRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ClubService
{
    public function __construct(
        protected ClubRepository $clubRepository
    ) {
    }

    public function lister(User $utilisateur)
    {
        return $this->clubRepository->listerParPresident($utilisateur);
    }

    public function creer(User $utilisateur, array $donnees, ?UploadedFile $logo = null): Club
    {
        $donnees['president_id'] = $utilisateur->id;
        $donnees['pays'] = $donnees['pays'] ?? 'Maroc';

        if ($logo) {
            $donnees['logo'] = $logo->store('clubs', 'public');
        }

        return $this->clubRepository->creer($donnees);
    }

    public function mettreAJour(Club $club, array $donnees, ?UploadedFile $logo = null): Club
    {
        if ($logo) {
            if ($club->logo) {
                Storage::disk('public')->delete($club->logo);
            }

            $donnees['logo'] = $logo->store('clubs', 'public');
        }

        return $this->clubRepository->mettreAJour($club, $donnees);
    }

    public function supprimer(Club $club): void
    {
        if ($club->logo) {
            Storage::disk('public')->delete($club->logo);
        }

        $this->clubRepository->supprimer($club);
    }
}
