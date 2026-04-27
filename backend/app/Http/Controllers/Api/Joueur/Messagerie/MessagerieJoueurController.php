<?php

namespace App\Http\Controllers\Api\Joueur\Messagerie;

use App\Http\Controllers\Controller;
use App\Http\Requests\Joueur\EnvoyerMessageJoueurRequest;
use App\Http\Requests\Joueur\ModifierMessageJoueurRequest;
use App\Http\Resources\Common\ApiErrorResource;
use App\Http\Resources\Common\ApiResponseResource;
use App\Http\Resources\Joueur\CanalJoueurCollection;
use App\Http\Resources\Joueur\MessageJoueurCollection;
use App\Models\Canal;
use App\Models\Message;
use App\Services\Joueur\Messagerie\MessagerieJoueurService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class MessagerieJoueurController extends Controller
{
    public function __construct(
        protected MessagerieJoueurService $messagerieJoueurService
    ) {
    }

    public function indexCanaux(): CanalJoueurCollection
    {
        $this->authorize('voirListe', Canal::class);

        return new CanalJoueurCollection(
            $this->messagerieJoueurService->listerCanaux(request()->user())
        );
    }

    public function indexMessages(Canal $canal): MessageJoueurCollection|JsonResponse
    {
        $this->authorize('voir', $canal);

        return new MessageJoueurCollection(
            $this->messagerieJoueurService->listerMessages($canal, request()->user())
        );
    }

    public function storeMessage(EnvoyerMessageJoueurRequest $request, Canal $canal): ApiResponseResource|JsonResponse
    {
        $this->authorize('voir', $canal);
        $message = $this->messagerieJoueurService->envoyerMessage($request->user(), $canal, $request->validated());

        return (new ApiResponseResource([
            'message' => 'Message envoye avec succes.',
            'data' => ['message' => $this->formaterMessage($message)],
        ]))->response()->setStatusCode(201);
    }

    public function updateMessage(ModifierMessageJoueurRequest $request, Message $message): ApiResponseResource|JsonResponse
    {
        $this->authorize('modifier', $message);
        $message = $this->messagerieJoueurService->modifierMessage($request->user(), $message, $request->validated());

        return new ApiResponseResource([
            'message' => 'Message modifie avec succes.',
            'data' => ['message' => $this->formaterMessage($message)],
        ]);
    }

    public function destroyMessage(Message $message): ApiResponseResource|JsonResponse
    {
        $this->authorize('supprimer', $message);
        $this->messagerieJoueurService->supprimerMessage(request()->user(), $message);

        return new ApiResponseResource(['message' => 'Message supprime avec succes.', 'data' => null]);
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
