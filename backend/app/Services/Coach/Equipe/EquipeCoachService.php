<?php

namespace App\Services\Coach\Equipe;

use App\Models\Equipe;
use App\Models\User;
use App\Repositories\Coach\Equipe\EquipeCoachRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EquipeCoachService
{
    public function __construct(
        protected EquipeCoachRepository $equipeCoachRepository
    ) {
    }

    public function listerEquipes(User $utilisateur)
    {
        return $this->equipeCoachRepository->listerEquipesCoach($utilisateur);
    }

    public function listerJoueursEquipe(User $utilisateur, Equipe $equipe)
    {
        return $this->equipeCoachRepository->listerJoueursEquipe($equipe);
    }

    public function afficherJoueurEquipe(User $utilisateur, Equipe $equipe, User $joueur): User
    {
        $this->verifierJoueurEquipe($equipe, $joueur);

        return $joueur->fresh();
    }

    public function creerJoueurEquipe(User $utilisateur, Equipe $equipe, array $donnees, ?UploadedFile $photo = null): User
    {
        $donnees['name'] = trim(($donnees['prenom'] ?? '').' '.($donnees['nom'] ?? ''));

        if ($photo) {
            $donnees['photo'] = $photo->store('profils', 'public');
        }

        return $this->equipeCoachRepository->creerJoueur($equipe, $donnees);
    }

    public function mettreAJourJoueurEquipe(User $utilisateur, Equipe $equipe, User $joueur, array $donnees, ?UploadedFile $photo = null): User
    {
        $this->verifierJoueurEquipe($equipe, $joueur);

        $donnees['name'] = trim(($donnees['prenom'] ?? '').' '.($donnees['nom'] ?? ''));

        if ($photo) {
            if ($joueur->photo) {
                Storage::disk('public')->delete($joueur->photo);
            }

            $donnees['photo'] = $photo->store('profils', 'public');
        }

        return $this->equipeCoachRepository->mettreAJourJoueur($joueur, $donnees);
    }

    public function retirerJoueurEquipe(User $utilisateur, Equipe $equipe, User $joueur): void
    {
        $this->verifierJoueurEquipe($equipe, $joueur);

        $this->equipeCoachRepository->retirerJoueur($equipe, $joueur);
    }

    protected function verifierJoueurEquipe(Equipe $equipe, User $joueur): void
    {
        if (! $this->equipeCoachRepository->trouverJoueurDansEquipe($equipe, $joueur)) {
            abort(404, 'Ce joueur n appartient pas a cette equipe.');
        }
    }
}
