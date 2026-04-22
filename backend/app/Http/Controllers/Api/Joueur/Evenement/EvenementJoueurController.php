<?php

namespace App\Http\Controllers\Api\Joueur\Evenement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Joueur\RepondreDisponibiliteRequest;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Http\Resources\Joueur\EvenementJoueurCollection;
use App\Models\Evenement;
use App\Services\Joueur\Evenement\EvenementJoueurService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class EvenementJoueurController extends Controller
{
    public function __construct(
        protected EvenementJoueurService $evenementJoueurService
    ) {
    }

    public function index(): EvenementJoueurCollection
    {
        return new EvenementJoueurCollection(
            $this->evenementJoueurService->listerEvenements(request()->user())
        );
    }

    public function composition(Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $composition = $this->evenementJoueurService->recupererCompositionMatch(request()->user(), $evenement);

            return new ApiResponseResource([
                'message' => 'Composition du match recuperee avec succes.',
                'data' => ['composition' => $composition],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function feuilleMatch(Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $feuilleMatch = $this->evenementJoueurService->recupererFeuilleMatch(request()->user(), $evenement);

            return new ApiResponseResource([
                'message' => 'Feuille de match recuperee avec succes.',
                'data' => ['feuille_match' => $feuilleMatch],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function statistiques(Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $statistiques = $this->evenementJoueurService->recupererStatistiquesMatchEvenement(request()->user(), $evenement);

            return new ApiResponseResource([
                'message' => 'Statistiques du match recuperees avec succes.',
                'data' => ['statistiques' => $statistiques],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function repondreDisponibilite(RepondreDisponibiliteRequest $request, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $disponibilite = $this->evenementJoueurService->repondreDisponibilite($request->user(), $evenement, $request->validated());

            return new ApiResponseResource([
                'message' => 'Disponibilite enregistree avec succes.',
                'data' => [
                    'disponibilite' => [
                        'id' => $disponibilite->id,
                        'evenement_id' => $disponibilite->evenement_id,
                        'utilisateur_id' => $disponibilite->utilisateur_id,
                        'reponse' => $disponibilite->reponse,
                        'commentaire' => $disponibilite->commentaire,
                        'date_reponse' => $disponibilite->date_reponse,
                    ],
                ],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }
}
