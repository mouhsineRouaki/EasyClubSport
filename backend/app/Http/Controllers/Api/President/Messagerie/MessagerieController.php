<?php

namespace App\Http\Controllers\Api\President\Messagerie;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Messagerie\CreerCanalRequest;
use App\Http\Requests\President\Messagerie\EnvoyerMessageRequest;
use App\Http\Requests\President\Messagerie\ModifierMessageRequest;
use App\Http\Resources\President\Messagerie\CanalCollection;
use App\Http\Resources\President\Messagerie\CanalResource;
use App\Http\Resources\President\Messagerie\MessageCollection;
use App\Http\Resources\President\Messagerie\MessageResource;
use App\Models\Canal;
use App\Models\Club;
use App\Models\Equipe;
use App\Models\Message;
use App\Models\User;
use App\Services\President\Messagerie\MessagerieService;
use Illuminate\Http\JsonResponse;

class MessagerieController extends Controller
{
    public function __construct(
        protected MessagerieService $messagerieService
    ) {
    }

    public function indexCanaux(): CanalCollection
    {
        $utilisateur = request()->user();
        $filtres = $this->cleanFilters($this->paginationParams());

        $this->authorize('voirListe', Canal::class);

        return new CanalCollection($this->messagerieService->listerCanaux($utilisateur, $filtres));
    }

    public function indexCanauxParEquipe(Club $club, Equipe $equipe): CanalCollection
    {
        $filtres = $this->cleanFilters($this->paginationParams());

        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('creer', [Canal::class, $equipe]);

        return new CanalCollection($this->messagerieService->listerCanauxParEquipe($equipe, $filtres));
    }

    public function storeCanal(CreerCanalRequest $request, Club $club, Equipe $equipe): CanalResource
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('creer', [Canal::class, $equipe]);

        $canal = $this->messagerieService->creerCanal(
            $request->user(),
            $equipe,
            $request->safe()->except('image'),
            $request->file('image')
        );

        return new CanalResource([
            'message' => 'Canal cree avec succes.',
            'canal' => $canal,
        ]);
    }

    public function participantsEquipe(Club $club, Equipe $equipe): JsonResponse
    {
        $this->verifierAppartenanceAuClub($club, $equipe);
        $this->authorize('creer', [Canal::class, $equipe]);

        return response()->json([
            'status' => true,
            'message' => 'Participants de l equipe recuperes avec succes.',
            'data' => [
                'participants' => $this->messagerieService->listerParticipantsEquipe(
                    $equipe,
                    (string) request()->query('q', '')
                ),
            ],
        ]);
    }

    public function showCanal(Canal $canal): CanalResource
    {
        $this->authorize('voir', $canal);

        return new CanalResource([
            'message' => 'Details du canal recuperes avec succes.',
            'canal' => $canal->load(['equipe.club', 'utilisateurs']),
        ]);
    }

    public function participantsCanal(Canal $canal): JsonResponse
    {
        $this->authorize('voir', $canal);

        return response()->json([
            'status' => true,
            'message' => 'Participants du canal recuperes avec succes.',
            'data' => [
                'participants' => $this->messagerieService->listerParticipantsCanal($canal),
            ],
        ]);
    }

    public function retirerParticipant(Canal $canal, User $participant): JsonResponse
    {
        $this->authorize('gerer', $canal);

        $this->messagerieService->retirerParticipant(request()->user(), $canal, $participant);

        return response()->json([
            'status' => true,
            'message' => 'Participant retire de la conversation avec succes.',
            'data' => null,
        ]);
    }

    public function indexMessages(Canal $canal): MessageCollection
    {
        $filtres = $this->cleanFilters($this->paginationParams(20, 100));

        $this->authorize('voir', $canal);

        return new MessageCollection($this->messagerieService->listerMessages($canal, $filtres));
    }

    public function storeMessage(EnvoyerMessageRequest $request, Canal $canal): MessageResource
    {
        $this->authorize('voir', $canal);

        $message = $this->messagerieService->envoyerMessage(
            $request->user(),
            $canal,
            $request->validated()
        );

        return new MessageResource([
            'message_text' => 'Message envoye avec succes.',
            'message' => $message,
        ]);
    }

    public function updateMessage(ModifierMessageRequest $request, Message $message): MessageResource
    {
        $this->authorize('modifier', $message);

        $message = $this->messagerieService->modifierMessage($message, $request->validated());

        return new MessageResource([
            'message_text' => 'Message modifie avec succes.',
            'message' => $message,
        ]);
    }

    public function destroyMessage(Message $message): JsonResponse
    {
        $this->authorize('supprimer', $message);

        $this->messagerieService->supprimerMessage($message);

        return response()->json([
            'status' => true,
            'message' => 'Message supprime avec succes.',
            'data' => null,
        ]);
    }

    protected function verifierAppartenanceAuClub(Club $club, Equipe $equipe): void
    {
        abort_if($equipe->club_id !== $club->id, 404, 'Equipe introuvable pour ce club.');
    }
}
