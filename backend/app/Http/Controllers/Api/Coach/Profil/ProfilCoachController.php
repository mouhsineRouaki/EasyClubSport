<?php

namespace App\Http\Controllers\Api\Coach\Profil;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\ModifierProfilCoachRequest;
use App\Http\Resources\Coach\ProfilCoachResource;
use App\Services\Coach\Profil\ProfilCoachService;

class ProfilCoachController extends Controller
{
    public function __construct(
        protected ProfilCoachService $profilCoachService
    ) {
    }

    public function afficher(): ProfilCoachResource
    {
        return new ProfilCoachResource([
            'message' => 'Profil coach recupere avec succes.',
            'utilisateur' => $this->profilCoachService->recupererProfil(request()->user()),
        ]);
    }

    public function modifier(ModifierProfilCoachRequest $request): ProfilCoachResource
    {
        $utilisateur = $this->profilCoachService->mettreAJourProfil(
            $request->user(),
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return new ProfilCoachResource([
            'message' => 'Profil coach modifie avec succes.',
            'utilisateur' => $utilisateur,
        ]);
    }
}
