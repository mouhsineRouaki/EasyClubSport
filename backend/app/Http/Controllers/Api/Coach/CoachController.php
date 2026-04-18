<?php

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\CreerConvocationCoachRequest;
use App\Http\Requests\Coach\CreerEvenementCoachRequest;
use App\Http\Requests\Coach\EnvoyerMessageCoachRequest;
use App\Http\Requests\Coach\ModifierConvocationCoachRequest;
use App\Http\Requests\Coach\ModifierEvenementCoachRequest;
use App\Http\Requests\Coach\ModifierMessageCoachRequest;
use App\Http\Requests\Coach\ModifierProfilCoachRequest;
use App\Http\Resources\Coach\CanalCoachCollection;
use App\Http\Resources\Coach\ConvocationCoachCollection;
use App\Http\Resources\Coach\DashboardCoachResource;
use App\Http\Resources\Coach\EquipeCoachCollection;
use App\Http\Resources\Coach\EvenementCoachCollection;
use App\Http\Resources\Coach\JoueurCoachCollection;
use App\Http\Resources\Coach\MessageCoachCollection;
use App\Http\Resources\Coach\NotificationCoachCollection;
use App\Http\Resources\Coach\ProfilCoachResource;
use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\Message;
use App\Models\Notification;
use App\Services\Coach\CoachService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CoachController extends Controller
{
    public function __construct(
        protected CoachService $coachService
    ) {
    }

    public function dashboard(): DashboardCoachResource
    {
        return new DashboardCoachResource(
            $this->coachService->recupererDashboard(request()->user())
        );
    }

    public function afficherProfil(): ProfilCoachResource
    {
        return new ProfilCoachResource([
            'message' => 'Profil coach recupere avec succes.',
            'utilisateur' => $this->coachService->recupererProfil(request()->user()),
        ]);
    }

    public function modifierProfil(ModifierProfilCoachRequest $request): ProfilCoachResource
    {
        $utilisateur = $this->coachService->mettreAJourProfil(
            $request->user(),
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return new ProfilCoachResource([
            'message' => 'Profil coach modifie avec succes.',
            'utilisateur' => $utilisateur,
        ]);
    }

    public function equipes(): EquipeCoachCollection
    {
        return new EquipeCoachCollection(
            $this->coachService->listerEquipes(request()->user())
        );
    }

    public function joueursEquipe(Equipe $equipe): JoueurCoachCollection|JsonResponse
    {
        try {
            return new JoueurCoachCollection(
                $this->coachService->listerJoueursEquipe(request()->user(), $equipe)
            );
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function evenementsEquipe(Equipe $equipe): EvenementCoachCollection|JsonResponse
    {
        try {
            return new EvenementCoachCollection(
                $this->coachService->listerEvenementsEquipe(request()->user(), $equipe)
            );
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function creerEvenement(CreerEvenementCoachRequest $request, Equipe $equipe): JsonResponse
    {
        try {
            $evenement = $this->coachService->creerEvenement($request->user(), $equipe, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Evenement cree avec succes.',
                'data' => ['evenement' => $evenement],
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function modifierEvenement(ModifierEvenementCoachRequest $request, Equipe $equipe, Evenement $evenement): JsonResponse
    {
        try {
            $evenement = $this->coachService->modifierEvenement($request->user(), $equipe, $evenement, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Evenement modifie avec succes.',
                'data' => ['evenement' => $evenement],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function supprimerEvenement(Equipe $equipe, Evenement $evenement): JsonResponse
    {
        try {
            $this->coachService->supprimerEvenement(request()->user(), $equipe, $evenement);

            return response()->json(['status' => true, 'message' => 'Evenement supprime avec succes.', 'data' => null]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function accepterInvitationEvenement(Evenement $evenement): JsonResponse
    {
        try {
            $evenement = $this->coachService->repondreInvitationAdversaire(request()->user(), $evenement, 'accepte');

            return response()->json([
                'status' => true,
                'message' => 'Invitation de match acceptee avec succes.',
                'data' => ['evenement' => $evenement],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Erreur de validation.', 'data' => $e->errors()], 422);
        }
    }

    public function refuserInvitationEvenement(Evenement $evenement): JsonResponse
    {
        try {
            $evenement = $this->coachService->repondreInvitationAdversaire(request()->user(), $evenement, 'refuse');

            return response()->json([
                'status' => true,
                'message' => 'Invitation de match refusee avec succes.',
                'data' => ['evenement' => $evenement],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Erreur de validation.', 'data' => $e->errors()], 422);
        }
    }

    public function convocationsEquipe(Equipe $equipe): ConvocationCoachCollection|JsonResponse
    {
        try {
            return new ConvocationCoachCollection(
                $this->coachService->listerConvocationsEquipe(request()->user(), $equipe)
            );
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function creerConvocations(CreerConvocationCoachRequest $request, Equipe $equipe, Evenement $evenement): JsonResponse
    {
        try {
            $convocations = $this->coachService->creerConvocations($request->user(), $equipe, $evenement, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Convocations creees avec succes.',
                'data' => [
                    'convocations' => $convocations,
                ],
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Erreur de validation.', 'data' => $e->errors()], 422);
        }
    }

    public function modifierConvocation(ModifierConvocationCoachRequest $request, Convocation $convocation): JsonResponse
    {
        try {
            $convocation = $this->coachService->modifierConvocation($request->user(), $convocation, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Convocation modifiee avec succes.',
                'data' => ['convocation' => $convocation],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function canaux(): CanalCoachCollection
    {
        return new CanalCoachCollection(
            $this->coachService->listerCanaux(request()->user())
        );
    }

    public function messages(Canal $canal): MessageCoachCollection|JsonResponse
    {
        try {
            return new MessageCoachCollection(
                $this->coachService->listerMessages(request()->user(), $canal)
            );
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function envoyerMessage(EnvoyerMessageCoachRequest $request, Canal $canal): JsonResponse
    {
        try {
            $message = $this->coachService->envoyerMessage($request->user(), $canal, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Message envoye avec succes.',
                'data' => ['message' => $this->formaterMessage($message)],
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function modifierMessage(ModifierMessageCoachRequest $request, Message $message): JsonResponse
    {
        try {
            $message = $this->coachService->modifierMessage($request->user(), $message, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Message modifie avec succes.',
                'data' => ['message' => $this->formaterMessage($message)],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function supprimerMessage(Message $message): JsonResponse
    {
        try {
            $this->coachService->supprimerMessage(request()->user(), $message);

            return response()->json(['status' => true, 'message' => 'Message supprime avec succes.', 'data' => null]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function notifications(): NotificationCoachCollection
    {
        return new NotificationCoachCollection(
            $this->coachService->listerNotifications(request()->user())
        );
    }

    public function marquerNotificationCommeLue(Notification $notification): JsonResponse
    {
        try {
            $notification = $this->coachService->marquerNotificationCommeLue(request()->user(), $notification);

            return response()->json(['status' => true, 'message' => 'Notification marquee comme lue avec succes.', 'data' => ['notification' => $notification]]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 403);
        }
    }

    public function marquerToutesNotificationsCommeLues(): JsonResponse
    {
        $total = $this->coachService->marquerToutesNotificationsCommeLues(request()->user());

        return response()->json(['status' => true, 'message' => 'Toutes les notifications ont ete marquees comme lues.', 'data' => ['notifications_mises_a_jour_total' => $total]]);
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
