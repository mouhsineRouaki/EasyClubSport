<?php

namespace App\Http\Controllers\Api\President\Profil;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Profil\ModifierProfilPresidentRequest;
use App\Http\Resources\President\Profil\ProfilPresidentResource;
use App\Services\President\Profil\ProfilPresidentService;

class ProfilPresidentController extends Controller
{
    public function __construct(
        protected ProfilPresidentService $profilPresidentService
    ) {
    }

    public function afficher(): ProfilPresidentResource
    {
        $utilisateur = request()->user();

        $this->authorize('voirProfilPresident', $utilisateur);

        return new ProfilPresidentResource([
            'message' => 'Profil president recupere avec succes.',
            'utilisateur' => $utilisateur,
        ]);
    }

    public function modifier(ModifierProfilPresidentRequest $request): ProfilPresidentResource
    {
        $utilisateur = $request->user();

        $this->authorize('modifierProfilPresident', $utilisateur);

        $utilisateur = $this->profilPresidentService->mettreAJour(
            $utilisateur,
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return new ProfilPresidentResource([
            'message' => 'Profil president modifie avec succes.',
            'utilisateur' => $utilisateur,
        ]);
    }
}

