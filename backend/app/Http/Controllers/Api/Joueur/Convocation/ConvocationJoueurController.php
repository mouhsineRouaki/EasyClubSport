<?php

namespace App\Http\Controllers\Api\Joueur\Convocation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Joueur\RepondreConvocationRequest;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Http\Resources\Joueur\ConvocationJoueurCollection;
use App\Models\Convocation;
use App\Services\Joueur\Convocation\ConvocationJoueurService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ConvocationJoueurController extends Controller
{
    public function __construct(
        protected ConvocationJoueurService $convocationJoueurService
    ) {
    }

    public function index(): ConvocationJoueurCollection
    {
        return new ConvocationJoueurCollection(
            $this->convocationJoueurService->listerConvocations(request()->user())
        );
    }

    public function repondre(RepondreConvocationRequest $request, Convocation $convocation): ApiResponseResource|JsonResponse
    {
        try {
            $convocation = $this->convocationJoueurService->repondreConvocation($request->user(), $convocation, $request->validated());

            return new ApiResponseResource([
                'message' => 'Reponse a la convocation enregistree avec succes.',
                'data' => [
                    'convocation' => [
                        'id' => $convocation->id,
                        'statut' => $convocation->statut,
                        'date_convocation' => $convocation->date_convocation,
                        'date_confirmation' => $convocation->date_confirmation,
                    ],
                ],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }
}
