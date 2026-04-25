<?php

namespace App\Http\Controllers\Api\Joueur\Profil;

use App\Http\Controllers\Controller;
use App\Http\Requests\Joueur\ModifierProfilJoueurRequest;
use App\Http\Resources\Joueur\ProfilJoueurResource;
use App\Services\Joueur\Equipe\EquipeJoueurService;
use App\Services\Joueur\Profil\ProfilJoueurService;

class ProfilJoueurController extends Controller
{
    public function __construct(
        protected ProfilJoueurService $profilJoueurService,
        protected EquipeJoueurService $equipeJoueurService
    ) {
    }

    public function afficher(): ProfilJoueurResource
    {
        $utilisateur = request()->user();
        $this->authorize('voirProfilJoueur', $utilisateur);

        return new ProfilJoueurResource([
            'message' => 'Profil joueur recupere avec succes.',
            'utilisateur' => $this->profilJoueurService->recupererProfil($utilisateur),
            'equipe' => $this->equipeJoueurService->recupererEquipe($utilisateur),
        ]);
    }

    public function modifier(ModifierProfilJoueurRequest $request): ProfilJoueurResource
    {
        $this->authorize('modifierProfilJoueur', $request->user());

        $resultat = $this->profilJoueurService->mettreAJourProfil(
            $request->user(),
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return new ProfilJoueurResource([
            'message' => 'Profil joueur modifie avec succes.',
            'utilisateur' => $resultat['utilisateur'],
            'equipe' => $resultat['equipe'],
        ]);
    }
}
