<?php

namespace App\Services\President\Equipe;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\User;
use App\Repositories\President\Equipe\EquipeRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipeService
{
    public function __construct(
        protected EquipeRepository $equipeRepository
    ) {
    }

    public function lister(Club $club, array $filtres = [])
    {
        return $this->equipeRepository->listerParClub($club, $filtres);
    }

    public function listerAdversaires(array $filtres = [])
    {
        return $this->equipeRepository->listerAdversaires($filtres);
    }

    public function creer(Club $club, array $donnees, ?UploadedFile $logo = null): Equipe
    {
        $donnees['club_id'] = $club->id;
        $donnees['statut'] = $donnees['statut'] ?? 'active';
        $donnees['code_invitation'] = $this->genererCodeInvitation();

        if ($logo) {
            $donnees['logo'] = $logo->store('equipes', 'public');
        }

        return $this->equipeRepository->creer($donnees);
    }

    public function mettreAJour(Equipe $equipe, array $donnees, ?UploadedFile $logo = null): Equipe
    {
        if ($logo) {
            if ($equipe->logo) {
                Storage::disk('public')->delete($equipe->logo);
            }

            $donnees['logo'] = $logo->store('equipes', 'public');
        }

        return $this->equipeRepository->mettreAJour($equipe, $donnees);
    }

    public function supprimer(Equipe $equipe): void
    {
        if ($equipe->logo) {
            Storage::disk('public')->delete($equipe->logo);
        }

        $this->equipeRepository->supprimer($equipe);
    }

    protected function genererCodeInvitation(): string
    {
        do {
            $codeInvitation = Str::upper(Str::random(8));
        } while ($this->equipeRepository->codeInvitationExiste($codeInvitation));

        return $codeInvitation;
    }

    public function assignerCoach(Equipe $equipe, User $coach): Equipe
    {
        if ($coach->role !== 'coach') {
            throw ValidationException::withMessages([
                'coach_id' => 'L utilisateur selectionne n est pas un coach.',
            ]);
        }

        return $this->equipeRepository->assignerCoach($equipe, $coach);
    }

    public function retirerCoach(Equipe $equipe): Equipe
    {
        return $this->equipeRepository->retirerCoach($equipe);
    }

    public function listerJoueurs(Equipe $equipe, array $filtres = [])
    {
        return $this->equipeRepository->listerJoueurs($equipe, $filtres);
    }

    public function ajouterJoueur(Equipe $equipe, User $joueur): void
    {
        if ($joueur->role !== 'joueur') {
            throw ValidationException::withMessages([
                'utilisateur_id' => 'L utilisateur selectionne n est pas un joueur.',
            ]);
        }

        if ($this->equipeRepository->joueurExisteDansEquipe($equipe, $joueur->id)) {
            throw ValidationException::withMessages([
                'utilisateur_id' => 'Ce joueur est deja dans cette equipe.',
            ]);
        }

        $membreEquipeExistant = $this->equipeRepository->trouverMembreEquipe($joueur->id);

        if ($membreEquipeExistant && $membreEquipeExistant->equipe_id !== $equipe->id) {
            throw ValidationException::withMessages([
                'utilisateur_id' => 'Ce joueur appartient deja a une autre equipe.',
            ]);
        }

        $this->equipeRepository->ajouterJoueur($equipe, $joueur);
    }

    public function retirerJoueur(Equipe $equipe, User $joueur): void
    {
        $this->equipeRepository->retirerJoueur($equipe, $joueur);
    }
}

