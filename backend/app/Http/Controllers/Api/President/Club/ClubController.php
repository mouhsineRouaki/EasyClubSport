<?php

namespace App\Http\Controllers\Api\President\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Club\CreerClubRequest;
use App\Http\Requests\President\Club\ModifierClubRequest;
use App\Http\Resources\President\Club\ClubCollection;
use App\Http\Resources\President\Club\ClubResource;
use App\Models\Club;
use App\Services\President\Club\ClubService;
use Illuminate\Http\JsonResponse;

class ClubController extends Controller
{
    public function __construct(
        protected ClubService $clubService
    ) {
    }

    public function index(): ClubCollection
    {
        $utilisateur = request()->user();
        $filtres = $this->cleanFilters($this->paginationParams());

        $this->authorize('voirListe', Club::class);

        return new ClubCollection($this->clubService->lister($utilisateur, $filtres));
    }

    public function store(CreerClubRequest $request): ClubResource
    {
        $utilisateur = $request->user();

        $this->authorize('creer', Club::class);

        $club = $this->clubService->creer(
            $utilisateur,
            $request->safe()->except('logo'),
            $request->file('logo')
        );

        return new ClubResource([
            'message' => 'Club cree avec succes.',
            'club' => $club,
        ]);
    }

    public function show(Club $club): ClubResource
    {
        $this->authorize('voir', $club);

        return new ClubResource([
            'message' => 'Details du club recuperes avec succes.',
            'club' => $club,
        ]);
    }

    public function update(ModifierClubRequest $request, Club $club): ClubResource
    {
        $this->authorize('modifier', $club);

        $club = $this->clubService->mettreAJour(
            $club,
            $request->safe()->except('logo'),
            $request->file('logo')
        );

        return new ClubResource([
            'message' => 'Club modifie avec succes.',
            'club' => $club,
        ]);
    }

    public function destroy(Club $club): JsonResponse
    {
        $this->authorize('supprimer', $club);

        $this->clubService->supprimer($club);

        return response()->json([
            'status' => true,
            'message' => 'Club supprime avec succes.',
            'data' => null,
        ]);
    }
}

