<?php

namespace App\Http\Controllers\Api\Coach\Evenement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\CreerEvenementCoachRequest;
use App\Http\Requests\Coach\EnregistrerCompositionMatchCoachRequest;
use App\Http\Requests\Coach\EnregistrerFeuilleMatchCoachRequest;
use App\Http\Requests\Coach\EnregistrerStatistiquesMatchCoachRequest;
use App\Http\Requests\Coach\ModifierEvenementCoachRequest;
use App\Http\Resources\Coach\EvenementCoachCollection;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Services\Coach\Evenement\EvenementCoachService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EvenementCoachController extends Controller
{
    public function __construct(
        protected EvenementCoachService $evenementCoachService
    ) {
    }

    public function index(Equipe $equipe): EvenementCoachCollection|JsonResponse
    {
        try {
            return new EvenementCoachCollection(
                $this->evenementCoachService->listerEvenementsEquipe(request()->user(), $equipe)
            );
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function disponibilites(Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $disponibilites = $this->evenementCoachService->listerDisponibilitesEvenement(request()->user(), $equipe, $evenement);

            return new ApiResponseResource([
                'message' => 'Disponibilites recuperees avec succes.',
                'data' => ['disponibilites' => $disponibilites],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function composition(Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $composition = $this->evenementCoachService->recupererCompositionMatch(request()->user(), $equipe, $evenement);

            return new ApiResponseResource([
                'message' => 'Composition du match recuperee avec succes.',
                'data' => ['composition' => $composition],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function enregistrerComposition(EnregistrerCompositionMatchCoachRequest $request, Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $composition = $this->evenementCoachService->enregistrerCompositionMatch($request->user(), $equipe, $evenement, $request->validated());

            return new ApiResponseResource([
                'message' => 'Composition du match enregistree avec succes.',
                'data' => ['composition' => $composition],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function feuilleMatch(Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $feuilleMatch = $this->evenementCoachService->recupererFeuilleMatch(request()->user(), $equipe, $evenement);

            return new ApiResponseResource([
                'message' => 'Feuille de match recuperee avec succes.',
                'data' => ['feuille_match' => $feuilleMatch],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function enregistrerFeuilleMatch(EnregistrerFeuilleMatchCoachRequest $request, Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $feuilleMatch = $this->evenementCoachService->enregistrerFeuilleMatch($request->user(), $equipe, $evenement, $request->validated());

            return new ApiResponseResource([
                'message' => 'Feuille de match enregistree avec succes.',
                'data' => ['feuille_match' => $feuilleMatch],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function statistiques(Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $statistiques = $this->evenementCoachService->recupererStatistiquesMatch(request()->user(), $equipe, $evenement);

            return new ApiResponseResource([
                'message' => 'Statistiques du match recuperees avec succes.',
                'data' => ['statistiques' => $statistiques],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function enregistrerStatistiques(EnregistrerStatistiquesMatchCoachRequest $request, Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $statistiques = $this->evenementCoachService->enregistrerStatistiquesMatch($request->user(), $equipe, $evenement, $request->validated());

            return new ApiResponseResource([
                'message' => 'Statistiques du match enregistrees avec succes.',
                'data' => ['statistiques' => $statistiques],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function creer(CreerEvenementCoachRequest $request, Equipe $equipe): ApiResponseResource|JsonResponse
    {
        try {
            $evenement = $this->evenementCoachService->creerEvenement($request->user(), $equipe, $request->validated());

            return (new ApiResponseResource([
                'message' => 'Evenement cree avec succes.',
                'data' => ['evenement' => $evenement],
            ]))->response()->setStatusCode(201);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function modifier(ModifierEvenementCoachRequest $request, Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $evenement = $this->evenementCoachService->modifierEvenement($request->user(), $equipe, $evenement, $request->validated());

            return new ApiResponseResource([
                'message' => 'Evenement modifie avec succes.',
                'data' => ['evenement' => $evenement],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function supprimer(Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $this->evenementCoachService->supprimerEvenement(request()->user(), $equipe, $evenement);

            return new ApiResponseResource(['message' => 'Evenement supprime avec succes.', 'data' => null]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function accepterInvitation(Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $evenement = $this->evenementCoachService->repondreInvitationAdversaire(request()->user(), $evenement, 'accepte');

            return new ApiResponseResource([
                'message' => 'Invitation de match acceptee avec succes.',
                'data' => ['evenement' => $evenement],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function refuserInvitation(Evenement $evenement): ApiResponseResource|JsonResponse
    {
        try {
            $evenement = $this->evenementCoachService->repondreInvitationAdversaire(request()->user(), $evenement, 'refuse');

            return new ApiResponseResource([
                'message' => 'Invitation de match refusee avec succes.',
                'data' => ['evenement' => $evenement],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }
}
