<?php

namespace App\Services\Joueur\Equipe;

use App\Models\User;
use App\Repositories\Joueur\Equipe\EquipeJoueurRepository;
use Illuminate\Validation\ValidationException;

class EquipeJoueurService
{
    public function __construct(
        protected EquipeJoueurRepository $equipeJoueurRepository
    ) {
    }

    public function recupererEquipe(User $utilisateur)
    {
        return $this->equipeJoueurRepository->recupererEquipeActive($utilisateur);
    }

    public function rejoindreEquipeParCode(User $utilisateur, string $codeInvitation)
    {
        if ($this->equipeJoueurRepository->utilisateurAPourEquipeActive($utilisateur)) {
            throw ValidationException::withMessages([
                'code_invitation' => 'Vous etes deja assigne a une equipe.',
            ]);
        }

        $equipe = $this->equipeJoueurRepository->trouverEquipeParCodeInvitation($codeInvitation);

        if (! $equipe) {
            throw ValidationException::withMessages([
                'code_invitation' => 'Code d invitation invalide.',
            ]);
        }

        if ($equipe->statut !== 'active') {
            throw ValidationException::withMessages([
                'code_invitation' => 'Cette equipe n accepte pas de nouvelles adhesions.',
            ]);
        }

        $this->equipeJoueurRepository->rattacherJoueurAEquipe($utilisateur, $equipe);

        return $this->equipeJoueurRepository->recupererEquipeActive($utilisateur);
    }
}
