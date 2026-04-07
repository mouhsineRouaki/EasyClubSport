<?php

namespace App\Services\President\Profil;

use App\Models\User;
use App\Repositories\President\Profil\ProfilPresidentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfilPresidentService
{
    public function __construct(
        protected ProfilPresidentRepository $profilPresidentRepository
    ) {
    }

    public function mettreAJour(User $utilisateur, array $donnees, ?UploadedFile $photo = null): User
    {
        $donnees['name'] = trim($donnees['prenom'].' '.$donnees['nom']);

        if ($photo) {
            if ($utilisateur->photo) {
                Storage::disk('public')->delete($utilisateur->photo);
            }

            $donnees['photo'] = $photo->store('profils', 'public');
        }

        return $this->profilPresidentRepository->mettreAJour($utilisateur, $donnees);
    }
}

