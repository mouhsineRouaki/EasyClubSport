<?php

namespace App\Http\Controllers\Api\President\Annonce;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Annonce\CreerAnnonceRequest;
use App\Http\Requests\President\Annonce\ModifierAnnonceRequest;
use App\Http\Resources\President\Annonce\AnnonceCollection;
use App\Http\Resources\President\Annonce\AnnonceResource;
use App\Models\Annonce;
use App\Models\Club;
use App\Services\President\Annonce\AnnonceService;
use Illuminate\Http\JsonResponse;

class AnnonceController extends Controller
{
    public function __construct(
        protected AnnonceService $annonceService
    ) {
    }

    public function index(): AnnonceCollection
    {
        $utilisateur = request()->user();

        $this->authorize('voirListe', Annonce::class);

        return new AnnonceCollection($this->annonceService->lister($utilisateur));
    }

    public function indexParClub(Club $club): AnnonceCollection
    {
        $this->authorize('creer', [Annonce::class, $club]);

        return new AnnonceCollection($this->annonceService->listerParClub($club));
    }

    public function store(CreerAnnonceRequest $request, Club $club): AnnonceResource
    {
        $this->authorize('creer', [Annonce::class, $club]);

        $annonce = $this->annonceService->creer(
            $request->user(),
            $club,
            $request->validated()
        );

        return new AnnonceResource([
            'message' => 'Annonce creee avec succes.',
            'annonce' => $annonce,
        ]);
    }

    public function show(Annonce $annonce): AnnonceResource
    {
        $this->authorize('voir', $annonce);

        return new AnnonceResource([
            'message' => 'Details de l annonce recuperes avec succes.',
            'annonce' => $annonce->load(['club', 'auteur']),
        ]);
    }

    public function update(ModifierAnnonceRequest $request, Annonce $annonce): AnnonceResource
    {
        $this->authorize('modifier', $annonce);

        $annonce = $this->annonceService->mettreAJour($annonce, $request->validated());

        return new AnnonceResource([
            'message' => 'Annonce modifiee avec succes.',
            'annonce' => $annonce,
        ]);
    }

    public function destroy(Annonce $annonce): JsonResponse
    {
        $this->authorize('supprimer', $annonce);

        $this->annonceService->supprimer($annonce);

        return response()->json([
            'status' => true,
            'message' => 'Annonce supprimee avec succes.',
            'data' => null,
        ]);
    }
}
