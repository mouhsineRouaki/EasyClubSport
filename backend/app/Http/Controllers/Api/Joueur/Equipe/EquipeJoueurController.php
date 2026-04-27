<?php

namespace App\Http\Controllers\Api\Joueur\Equipe;

use App\Http\Controllers\Controller;
use App\Http\Requests\Joueur\RejoindreEquipeJoueurRequest;
use App\Http\Resources\Joueur\EquipeJoueurResource;
use App\Models\Equipe;
use App\Services\Joueur\Equipe\EquipeJoueurService;

class EquipeJoueurController extends Controller
{
    public function __construct(
        protected EquipeJoueurService $equipeJoueurService
    ) {
    }

    public function afficher(): EquipeJoueurResource
    {
        $this->authorize('voirCommeJoueur', Equipe::class);

        return new EquipeJoueurResource([
            'message' => 'Equipe du joueur recuperee avec succes.',
            'equipe' => $this->equipeJoueurService->recupererEquipe(request()->user()),
        ]);
    }

    public function rejoindre(RejoindreEquipeJoueurRequest $request): EquipeJoueurResource
    {
        $this->authorize('rejoindreCommeJoueur', Equipe::class);

        $equipe = $this->equipeJoueurService->rejoindreEquipeParCode(
            $request->user(),
            strtoupper(trim($request->string('code_invitation')->toString()))
        );

        return new EquipeJoueurResource([
            'message' => 'Equipe rejointe avec succes.',
            'equipe' => $equipe,
        ]);
    }
}
