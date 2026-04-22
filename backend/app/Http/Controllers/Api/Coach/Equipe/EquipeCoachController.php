<?php

namespace App\Http\Controllers\Api\Coach\Equipe;

use App\Http\Controllers\Controller;
use App\Http\Resources\Coach\EquipeCoachCollection;
use App\Http\Resources\Coach\JoueurCoachCollection;
use App\Http\Resources\Common\ApiErrorResource;
use App\Models\Equipe;
use App\Services\Coach\Equipe\EquipeCoachService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class EquipeCoachController extends Controller
{
    public function __construct(
        protected EquipeCoachService $equipeCoachService
    ) {
    }

    public function index(): EquipeCoachCollection
    {
        return new EquipeCoachCollection(
            $this->equipeCoachService->listerEquipes(request()->user())
        );
    }

    public function joueurs(Equipe $equipe): JoueurCoachCollection|JsonResponse
    {
        try {
            return new JoueurCoachCollection(
                $this->equipeCoachService->listerJoueursEquipe(request()->user(), $equipe)
            );
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }
}
