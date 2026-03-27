<?php

namespace App\Services\President;

use App\Models\Club;
use App\Models\Equipe;
use App\Repositories\President\EquipeRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipeService
{
    public function __construct(
        protected EquipeRepository $equipeRepository
    ) {
    }

    public function lister(Club $club)
    {
        return $this->equipeRepository->listerParClub($club);
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
}
