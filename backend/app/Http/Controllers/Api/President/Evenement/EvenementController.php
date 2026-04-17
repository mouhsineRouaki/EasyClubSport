<?php

namespace App\Http\Controllers\Api\President\Evenement;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Evenement\CreerEvenementRequest;
use App\Http\Requests\President\Evenement\ModifierEvenementRequest;
use App\Http\Resources\President\Evenement\EvenementCollection;
use App\Http\Resources\President\Evenement\EvenementResource;
use App\Models\Club;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Services\President\Evenement\EvenementService;
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
        $filtres = $this->cleanFilters(array_merge(
            $this->paginationParams(),
            request()->only(['q', 'statut', 'type', 'date_debut', 'date_fin'])
        ));

        $this->authorize('voirListe', Evenement::class);

        return new EvenementCollection($this->evenementService->lister($utilisateur, $filtres));
    }

    public function indexParEquipe(Club $club, Equipe $equipe): EvenementCollection
    {
        $filtres = $this->cleanFilters(array_merge(
            $this->paginationParams(),
            request()->only(['q', 'statut', 'type', 'date_debut', 'date_fin'])
        ));

        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('creer', [Evenement::class, $equipe]);

        return new EvenementCollection($this->evenementService->listerParEquipe($equipe, $filtres));
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
            'evenement' => $evenement->load(['equipe.club', 'adversaireEquipe.club']),
        ]);
    }

    public function show(Club $club, Equipe $equipe, Evenement $evenement): EvenementResource
    {
        $this->verifierAppartenanceEvenement($club, $equipe, $evenement);
        $this->authorize('voir', $evenement);

        return new EvenementResource([
            'message' => 'Details de l evenement recuperes avec succes.',
            'evenement' => $evenement->load(['equipe.club', 'adversaireEquipe.club']),
        ]);
    }

    public function update(ModifierEvenementRequest $request, Club $club, Equipe $equipe, Evenement $evenement): EvenementResource
    {
        $this->verifierAppartenanceEvenement($club, $equipe, $evenement);
        $this->authorize('modifier', $evenement);

        $evenement = $this->evenementService->mettreAJour($evenement, $request->validated());

        return new EvenementResource([
            'message' => 'Evenement modifie avec succes.',
            'evenement' => $evenement->load(['equipe.club', 'adversaireEquipe.club']),
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

    public function accepterInvitation(Evenement $evenement): EvenementResource
    {
        $evenement = $this->evenementService->repondreInvitationAdversaire(request()->user(), $evenement, 'accepte');

        return new EvenementResource([
            'message' => 'Invitation de match acceptee avec succes.',
            'evenement' => $evenement->load(['equipe.club', 'adversaireEquipe.club', 'invitationAdversaireReponduePar']),
        ]);
    }

    public function refuserInvitation(Evenement $evenement): EvenementResource
    {
        $evenement = $this->evenementService->repondreInvitationAdversaire(request()->user(), $evenement, 'refuse');

        return new EvenementResource([
            'message' => 'Invitation de match refusee avec succes.',
            'evenement' => $evenement->load(['equipe.club', 'adversaireEquipe.club', 'invitationAdversaireReponduePar']),
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

