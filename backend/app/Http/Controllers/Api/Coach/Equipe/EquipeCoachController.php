<?php

namespace App\Http\Controllers\Api\Coach\Equipe;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\CreerJoueurCoachRequest;
use App\Http\Requests\Coach\ModifierJoueurCoachRequest;
use App\Http\Resources\Coach\EquipeCoachCollection;
use App\Http\Resources\Coach\JoueurCoachCollection;
use App\Http\Resources\Coach\JoueurCoachResource;
use App\Models\Equipe;
use App\Models\User;
use App\Services\Coach\Equipe\EquipeCoachService;
use Illuminate\Http\JsonResponse;

class EquipeCoachController extends Controller
{
    public function __construct(
        protected EquipeCoachService $equipeCoachService
    ) {
    }

    public function index(): EquipeCoachCollection
    {
        $this->authorize('voirListeCoach', Equipe::class);

        return new EquipeCoachCollection(
            $this->equipeCoachService->listerEquipes(request()->user())
        );
    }

    public function joueurs(Equipe $equipe): JoueurCoachCollection
    {
        $this->authorize('gererCommeCoach', $equipe);

        return new JoueurCoachCollection(
            $this->equipeCoachService->listerJoueursEquipe(request()->user(), $equipe)
        );
    }

    public function showJoueur(Equipe $equipe, User $joueur): JoueurCoachResource
    {
        $this->authorize('gererCommeCoach', $equipe);
        $joueur = $this->equipeCoachService->afficherJoueurEquipe(request()->user(), $equipe, $joueur);

        return new JoueurCoachResource([
            'message' => 'Details du joueur recuperes avec succes.',
            'joueur' => $joueur,
        ]);
    }

    public function storeJoueur(CreerJoueurCoachRequest $request, Equipe $equipe): JoueurCoachResource|JsonResponse
    {
        $this->authorize('gererCommeCoach', $equipe);
        $joueur = $this->equipeCoachService->creerJoueurEquipe(
            $request->user(),
            $equipe,
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return (new JoueurCoachResource([
            'message' => 'Joueur cree avec succes.',
            'joueur' => $joueur,
        ]))->response()->setStatusCode(201);
    }

    public function updateJoueur(ModifierJoueurCoachRequest $request, Equipe $equipe, User $joueur): JoueurCoachResource|JsonResponse
    {
        $this->authorize('gererCommeCoach', $equipe);
        $joueur = $this->equipeCoachService->mettreAJourJoueurEquipe(
            $request->user(),
            $equipe,
            $joueur,
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return new JoueurCoachResource([
            'message' => 'Joueur modifie avec succes.',
            'joueur' => $joueur,
        ]);
    }

    public function destroyJoueur(Equipe $equipe, User $joueur): JsonResponse
    {
        $this->authorize('gererCommeCoach', $equipe);
        $this->equipeCoachService->retirerJoueurEquipe(request()->user(), $equipe, $joueur);

        return response()->json([
            'status' => true,
            'message' => 'Joueur retire de l equipe avec succes.',
            'data' => null,
        ]);
    }
}
