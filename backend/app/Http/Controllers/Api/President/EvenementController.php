<?php

namespace App\Http\Controllers\Api\President;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\CreerEvenementRequest;
use App\Http\Requests\President\ModifierEvenementRequest;
use App\Http\Resources\President\EvenementCollection;
use App\Http\Resources\President\EvenementResource;
use App\Models\Club;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Services\President\EvenementService;
use Illuminate\Http\JsonResponse;

class EvenementController extends Controller
{
    public function __construct(
        protected EvenementService $evenementService
    ) {
    }

    public function index(): EvenementCollection
    {
        $utilisateur = request()->user();

        $this->authorize('voirListe', Evenement::class);

        return new EvenementCollection($this->evenementService->lister($utilisateur));
    }

    public function indexParEquipe(Club $club, Equipe $equipe): EvenementCollection
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('creer', [Evenement::class, $equipe]);

        return new EvenementCollection($this->evenementService->listerParEquipe($equipe));
    }

    public function store(CreerEvenementRequest $request, Club $club, Equipe $equipe): EvenementResource
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('creer', [Evenement::class, $equipe]);

        $evenement = $this->evenementService->creer(
            $request->user(),
            $equipe,
            $request->validated()
        );

        return new EvenementResource([
            'message' => 'Evenement cree avec succes.',
            'evenement' => $evenement,
        ]);
    }

    public function show(Club $club, Equipe $equipe, Evenement $evenement): EvenementResource
    {
        $this->verifierAppartenanceEvenement($club, $equipe, $evenement);
        $this->authorize('voir', $evenement);

        return new EvenementResource([
            'message' => 'Details de l evenement recuperes avec succes.',
            'evenement' => $evenement->load('equipe.club'),
        ]);
    }

    public function update(ModifierEvenementRequest $request, Club $club, Equipe $equipe, Evenement $evenement): EvenementResource
    {
        $this->verifierAppartenanceEvenement($club, $equipe, $evenement);
        $this->authorize('modifier', $evenement);

        $evenement = $this->evenementService->mettreAJour($evenement, $request->validated());

        return new EvenementResource([
            'message' => 'Evenement modifie avec succes.',
            'evenement' => $evenement,
        ]);
    }

    public function destroy(Club $club, Equipe $equipe, Evenement $evenement): JsonResponse
    {
        $this->verifierAppartenanceEvenement($club, $equipe, $evenement);
        $this->authorize('supprimer', $evenement);

        $this->evenementService->supprimer($evenement);

        return response()->json([
            'status' => true,
            'message' => 'Evenement supprime avec succes.',
            'data' => null,
        ]);
    }

    protected function verifierAppartenanceAuClub(Club $club, Equipe $equipe): void
    {
        abort_if($equipe->club_id !== $club->id, 404, 'Equipe introuvable pour ce club.');
    }

    protected function verifierAppartenanceEvenement(Club $club, Equipe $equipe, Evenement $evenement): void
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        abort_if($evenement->equipe_id !== $equipe->id, 404, 'Evenement introuvable pour cette equipe.');
    }
}
