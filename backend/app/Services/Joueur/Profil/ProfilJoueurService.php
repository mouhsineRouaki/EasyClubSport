<?php

namespace App\Services\Joueur\Profil;

use App\Models\User;
use App\Repositories\Joueur\Equipe\EquipeJoueurRepository;
use App\Repositories\Joueur\Profil\ProfilJoueurRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfilJoueurService
{
    public function __construct(
        protected ProfilJoueurRepository $profilJoueurRepository,
        protected EquipeJoueurRepository $equipeJoueurRepository
    ) {
    }

    public function recupererProfil(User $utilisateur): User
    {
        return $this->profilJoueurRepository->recupererProfil($utilisateur);
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees, ?UploadedFile $photo = null): array
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

        $utilisateur = $this->profilJoueurRepository->mettreAJourProfil($utilisateur, $donnees);

        return [
            'utilisateur' => $utilisateur,
            'equipe' => $this->equipeJoueurRepository->recupererEquipeActive($utilisateur),
        ];
    }
}
