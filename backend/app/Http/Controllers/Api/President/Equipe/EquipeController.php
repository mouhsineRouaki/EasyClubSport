<?php

namespace App\Http\Controllers\Api\President\Equipe;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Equipe\AjouterJoueurEquipeRequest;
use App\Http\Requests\President\Equipe\AssignerCoachEquipeRequest;
use App\Http\Requests\President\Equipe\CreerEquipeRequest;
use App\Http\Requests\President\Equipe\ModifierEquipeRequest;
use App\Http\Resources\President\Equipe\EquipeCollection;
use App\Http\Resources\President\Equipe\EquipeResource;
use App\Http\Resources\President\Equipe\JoueurEquipeCollection;
use App\Models\Club;
use App\Models\Equipe;
use App\Models\User;
use App\Services\President\Equipe\EquipeService;
use Illuminate\Http\JsonResponse;

class EquipeController extends Controller
{
    public function __construct(
        protected EquipeService $equipeService
    ) {
    }

    public function index(Club $club): EquipeCollection
    {
        $filtres = $this->cleanFilters(array_merge(
            $this->paginationParams(),
            request()->only(['statut'])
        ));

        $this->authorize('voirListe', [Equipe::class, $club]);

        return new EquipeCollection($this->equipeService->lister($club, $filtres));
    }

    public function adversaires(): EquipeCollection
    {
        $filtres = $this->cleanFilters(array_merge(
            $this->paginationParams(20, 100),
            request()->only(['exclude_equipe_id'])
        ));

        return new EquipeCollection($this->equipeService->listerAdversaires($filtres));
    }

    public function store(CreerEquipeRequest $request, Club $club): EquipeResource
    {
        $this->authorize('creer', [Equipe::class, $club]);

        $equipe = $this->equipeService->creer(
            $club,
            $request->safe()->except('logo'),
            $request->file('logo')
        );

        return new EquipeResource([
            'message' => 'Equipe creee avec succes.',
            'equipe' => $equipe,
        ]);
    }

    public function show(Club $club, Equipe $equipe): EquipeResource
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('voir', $equipe);

        return new EquipeResource([
            'message' => 'Details de l equipe recuperes avec succes.',
            'equipe' => $equipe,
        ]);
    }

    public function update(ModifierEquipeRequest $request, Club $club, Equipe $equipe): EquipeResource
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('modifier', $equipe);

        $equipe = $this->equipeService->mettreAJour(
            $equipe,
            $request->safe()->except('logo'),
            $request->file('logo')
        );

        return new EquipeResource([
            'message' => 'Equipe modifiee avec succes.',
            'equipe' => $equipe,
        ]);
    }

    public function destroy(Club $club, Equipe $equipe): JsonResponse
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('supprimer', $equipe);

        $this->equipeService->supprimer($equipe);

        return response()->json([
            'status' => true,
            'message' => 'Equipe supprimee avec succes.',
            'data' => null,
        ]);
    }

    public function assignerCoach(AssignerCoachEquipeRequest $request, Club $club, Equipe $equipe): EquipeResource
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('gererCoach', $equipe);

        $coach = User::findOrFail($request->integer('coach_id'));
        $equipe = $this->equipeService->assignerCoach($equipe, $coach);

        return new EquipeResource([
            'message' => 'Coach assigne a l equipe avec succes.',
            'equipe' => $equipe,
        ]);
    }

    public function retirerCoach(Club $club, Equipe $equipe): EquipeResource
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('gererCoach', $equipe);

        $equipe = $this->equipeService->retirerCoach($equipe);

        return new EquipeResource([
            'message' => 'Coach retire de l equipe avec succes.',
            'equipe' => $equipe,
        ]);
    }

    public function listerJoueurs(Club $club, Equipe $equipe): JoueurEquipeCollection
    {
        $filtres = $this->cleanFilters($this->paginationParams());

        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('gererJoueurs', $equipe);

        return new JoueurEquipeCollection($this->equipeService->listerJoueurs($equipe, $filtres));
    }

    public function ajouterJoueur(AjouterJoueurEquipeRequest $request, Club $club, Equipe $equipe): JsonResponse
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('gererJoueurs', $equipe);

        $joueur = User::findOrFail($request->integer('utilisateur_id'));
        $this->equipeService->ajouterJoueur($equipe, $joueur);

        return response()->json([
            'status' => true,
            'message' => 'Joueur ajoute a l equipe avec succes.',
            'data' => null,
        ]);
    }

    public function retirerJoueur(Club $club, Equipe $equipe, User $joueur): JsonResponse
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('gererJoueurs', $equipe);

        $this->equipeService->retirerJoueur($equipe, $joueur);

        return response()->json([
            'status' => true,
            'message' => 'Joueur retire de l equipe avec succes.',
            'data' => null,
        ]);
    }

    protected function verifierAppartenanceAuClub(Club $club, Equipe $equipe): void
    {
        abort_if($equipe->club_id !== $club->id, 404, 'Equipe introuvable pour ce club.');
    }
}

