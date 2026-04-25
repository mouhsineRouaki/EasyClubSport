<?php

namespace App\Http\Controllers\Api\Coach\Convocation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\CreerConvocationCoachRequest;
use App\Http\Requests\Coach\ModifierConvocationCoachRequest;
use App\Http\Resources\Coach\ConvocationCoachCollection;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Services\Coach\Convocation\ConvocationCoachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConvocationCoachController extends Controller
{
    public function __construct(
        protected ConvocationCoachService $convocationCoachService
    ) {
    }

    public function index(Equipe $equipe): ConvocationCoachCollection
    {
        $this->authorize('gererCommeCoach', $equipe);

        return new ConvocationCoachCollection(
            $this->convocationCoachService->listerConvocationsEquipe(request()->user(), $equipe)
        );
    }

    public function creer(CreerConvocationCoachRequest $request, Equipe $equipe, Evenement $evenement): ApiResponseResource|JsonResponse
    {
        $this->authorize('gererCommeCoach', $equipe);
        $this->authorize('creer', [Convocation::class, $evenement]);

        try {
            $convocations = $this->convocationCoachService->creerConvocations($request->user(), $equipe, $evenement, $request->validated());

            return (new ApiResponseResource([
                'message' => 'Convocations creees avec succes.',
                'data' => ['convocations' => $convocations],
            ]))->response()->setStatusCode(201);
        } catch (ValidationException $e) {
            return (new ApiErrorResource(['message' => 'Erreur de validation.', 'data' => $e->errors()]))->response()->setStatusCode(422);
        }
    }

    public function modifier(ModifierConvocationCoachRequest $request, Convocation $convocation): ApiResponseResource|JsonResponse
    {
        $this->authorize('modifier', $convocation);

        $convocation = $this->convocationCoachService->modifierConvocation($request->user(), $convocation, $request->validated());

        return new ApiResponseResource([
            'message' => 'Convocation modifiee avec succes.',
            'data' => ['convocation' => $convocation],
        ]);
    }
}
