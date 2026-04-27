<?php

namespace App\Services\Coach\Profil;

use App\Models\User;
use App\Repositories\Coach\Profil\ProfilCoachRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfilCoachService
{
    public function __construct(
        protected ProfilCoachRepository $profilCoachRepository
    ) {
    }

    public function recupererProfil(User $utilisateur): User
    {
        return $this->profilCoachRepository->recupererProfil($utilisateur);
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees, ?UploadedFile $photo = null): User
    {
        if (isset($donnees['nom']) || isset($donnees['prenom'])) {
            $nom = $donnees['nom'] ?? $utilisateur->nom;
            $prenom = $donnees['prenom'] ?? $utilisateur->prenom;
            $donnees['name'] = trim($prenom.' '.$nom);
        }

        if ($photo) {
            if ($utilisateur->photo) {
                Storage::disk('public')->delete($utilisateur->photo);
            }

            $donnees['photo'] = $photo->store('profils', 'public');
        }

        return $this->profilCoachRepository->mettreAJourProfil($utilisateur, $donnees);
    }
}
