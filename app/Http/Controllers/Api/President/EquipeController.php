<?php

namespace App\Http\Controllers\Api\President;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\CreerEquipeRequest;
use App\Http\Requests\President\ModifierEquipeRequest;
use App\Http\Resources\President\EquipeCollection;
use App\Http\Resources\President\EquipeResource;
use App\Models\Club;
use App\Models\Equipe;
use App\Services\President\EquipeService;
use Illuminate\Http\JsonResponse;

class EquipeController extends Controller
{
    public function __construct(
        protected EquipeService $equipeService
    ) {
    }

    public function index(Club $club): EquipeCollection
    {
        $this->authorize('voirListe', [Equipe::class, $club]);

        return new EquipeCollection($this->equipeService->lister($club));
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

    protected function verifierAppartenanceAuClub(Club $club, Equipe $equipe): void
    {
        abort_if($equipe->club_id !== $club->id, 404, 'Equipe introuvable pour ce club.');
    }
}
