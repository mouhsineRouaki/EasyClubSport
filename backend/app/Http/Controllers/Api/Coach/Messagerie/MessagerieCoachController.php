<?php

namespace App\Http\Controllers\Api\Coach\Messagerie;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\EnvoyerMessageCoachRequest;
use App\Http\Requests\Coach\ModifierMessageCoachRequest;
use App\Http\Resources\Coach\CanalCoachCollection;
use App\Http\Resources\Coach\MessageCoachCollection;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Models\Canal;
use App\Models\Message;
use App\Services\Coach\Messagerie\MessagerieCoachService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class MessagerieCoachController extends Controller
{
    public function __construct(
        protected MessagerieCoachService $messagerieCoachService
    ) {
    }

    public function indexCanaux(): CanalCoachCollection
    {
        return new CanalCoachCollection(
            $this->messagerieCoachService->listerCanaux(request()->user())
        );
    }

    public function indexMessages(Canal $canal): MessageCoachCollection|JsonResponse
    {
        try {
            return new MessageCoachCollection(
                $this->messagerieCoachService->listerMessages(request()->user(), $canal)
            );
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function storeMessage(EnvoyerMessageCoachRequest $request, Canal $canal): ApiResponseResource|JsonResponse
    {
        try {
            $message = $this->messagerieCoachService->envoyerMessage($request->user(), $canal, $request->validated());

            return (new ApiResponseResource([
                'message' => 'Message envoye avec succes.',
                'data' => ['message' => $this->formaterMessage($message)],
            ]))->response()->setStatusCode(201);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function updateMessage(ModifierMessageCoachRequest $request, Message $message): ApiResponseResource|JsonResponse
    {
        try {
            $message = $this->messagerieCoachService->modifierMessage($request->user(), $message, $request->validated());

            return new ApiResponseResource([
                'message' => 'Message modifie avec succes.',
                'data' => ['message' => $this->formaterMessage($message)],
            ]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    public function destroyMessage(Message $message): ApiResponseResource|JsonResponse
    {
        try {
            $this->messagerieCoachService->supprimerMessage(request()->user(), $message);

            return new ApiResponseResource(['message' => 'Message supprime avec succes.', 'data' => null]);
        } catch (AuthorizationException $e) {
            return (new ApiErrorResource(['message' => $e->getMessage()]))->response()->setStatusCode(403);
        }
    }

    protected function formaterMessage(Message $message): array
    {
        $message = $message->fresh(['expediteur', 'equipe.club', 'canal']);

        return [
            'id' => $message->id,
            'canal_id' => $message->canal_id,
            'equipe_id' => $message->equipe_id,
            'expediteur_id' => $message->expediteur_id,
            'contenu' => $message->contenu,
            'type_message' => $message->type_message,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'expediteur' => $message->expediteur ? [
                'id' => $message->expediteur->id,
                'nom' => trim(($message->expediteur->prenom ?? '').' '.($message->expediteur->nom ?? '')),
                'email' => $message->expediteur->email,
            ] : null,
            'equipe' => $message->equipe ? [
                'id' => $message->equipe->id,
                'nom' => $message->equipe->nom,
            ] : null,
            'club' => $message->equipe?->club ? [
                'id' => $message->equipe->club->id,
                'nom' => $message->equipe->club->nom,
            ] : null,
        ];
    }
}
