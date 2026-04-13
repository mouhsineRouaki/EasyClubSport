<?php

namespace App\Http\Controllers\Api\Joueur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Joueur\EnvoyerMessageJoueurRequest;
use App\Http\Requests\Joueur\ModifierMessageJoueurRequest;
use App\Http\Requests\Joueur\ModifierProfilJoueurRequest;
use App\Http\Requests\Joueur\RepondreConvocationRequest;
use App\Http\Requests\Joueur\RepondreDisponibiliteRequest;
use App\Http\Resources\Joueur\CanalJoueurCollection;
use App\Http\Resources\Joueur\ConvocationJoueurCollection;
use App\Http\Resources\Joueur\DashboardJoueurResource;
use App\Http\Resources\Joueur\DocumentJoueurCollection;
use App\Http\Resources\Joueur\EquipeJoueurResource;
use App\Http\Resources\Joueur\EvenementJoueurCollection;
use App\Http\Resources\Joueur\MessageJoueurCollection;
use App\Http\Resources\Joueur\NotificationJoueurCollection;
use App\Http\Resources\Joueur\ProfilJoueurResource;
use App\Http\Resources\Joueur\StatistiqueJoueurCollection;
use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Evenement;
use App\Models\Message;
use App\Models\Notification;
use App\Services\Joueur\JoueurService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class JoueurController extends Controller
{
    public function __construct(
        protected JoueurService $joueurService
    ) {
    }

    public function dashboard(): DashboardJoueurResource
    {
        return new DashboardJoueurResource(
            $this->joueurService->recupererDashboard(request()->user())
        );
    }

    public function afficherProfil(): ProfilJoueurResource
    {
        $utilisateur = request()->user();

        return new ProfilJoueurResource([
            'message' => 'Profil joueur recupere avec succes.',
            'utilisateur' => $this->joueurService->recupererProfil($utilisateur),
            'equipe' => $this->joueurService->recupererEquipe($utilisateur),
        ]);
    }

    public function modifierProfil(ModifierProfilJoueurRequest $request): ProfilJoueurResource
    {
        $resultat = $this->joueurService->mettreAJourProfil(
            $request->user(),
            $request->safe()->except('photo'),
            $request->file('photo')
        );

        return new ProfilJoueurResource([
            'message' => 'Profil joueur modifie avec succes.',
            'utilisateur' => $resultat['utilisateur'],
            'equipe' => $resultat['equipe'],
        ]);
    }

    public function equipe(): EquipeJoueurResource
    {
        return new EquipeJoueurResource([
            'message' => 'Equipe du joueur recuperee avec succes.',
            'equipe' => $this->joueurService->recupererEquipe(request()->user()),
        ]);
    }

    public function evenements(): EvenementJoueurCollection
    {
        return new EvenementJoueurCollection(
            $this->joueurService->listerEvenements(request()->user())
        );
    }

    public function repondreDisponibilite(RepondreDisponibiliteRequest $request, Evenement $evenement): JsonResponse
    {
        try {
            $disponibilite = $this->joueurService->repondreDisponibilite(
                $request->user(),
                $evenement,
                $request->validated()
            );

            return response()->json([
                'status' => true,
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
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function convocations(): ConvocationJoueurCollection
    {
        return new ConvocationJoueurCollection(
            $this->joueurService->listerConvocations(request()->user())
        );
    }

    public function repondreConvocation(RepondreConvocationRequest $request, Convocation $convocation): JsonResponse
    {
        try {
            $convocation = $this->joueurService->repondreConvocation(
                $request->user(),
                $convocation,
                $request->validated()
            );

            return response()->json([
                'status' => true,
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
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function documents(): DocumentJoueurCollection
    {
        return new DocumentJoueurCollection(
            $this->joueurService->listerDocuments(request()->user())
        );
    }

    public function canaux(): CanalJoueurCollection
    {
        return new CanalJoueurCollection(
            $this->joueurService->listerCanaux(request()->user())
        );
    }

    public function messages(Canal $canal): JsonResponse|MessageJoueurCollection
    {
        try {
            return new MessageJoueurCollection(
                $this->joueurService->listerMessages($canal, request()->user())
            );
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function envoyerMessage(EnvoyerMessageJoueurRequest $request, Canal $canal): JsonResponse
    {
        try {
            $message = $this->joueurService->envoyerMessage(
                $request->user(),
                $canal,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Message envoye avec succes.',
                'data' => [
                    'message' => [
                        'id' => $message->id,
                        'equipe_id' => $message->equipe_id,
                        'expediteur_id' => $message->expediteur_id,
                        'contenu' => $message->contenu,
                        'type_message' => $message->type_message,
                    ],
                ],
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function modifierMessage(ModifierMessageJoueurRequest $request, Message $message): JsonResponse
    {
        try {
            $message = $this->joueurService->modifierMessage(
                $request->user(),
                $message,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Message modifie avec succes.',
                'data' => [
                    'message' => [
                        'id' => $message->id,
                        'contenu' => $message->contenu,
                        'updated_at' => $message->updated_at,
                    ],
                ],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function supprimerMessage(Message $message): JsonResponse
    {
        try {
            $this->joueurService->supprimerMessage(request()->user(), $message);

            return response()->json([
                'status' => true,
                'message' => 'Message supprime avec succes.',
                'data' => null,
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function notifications(): NotificationJoueurCollection
    {
        return new NotificationJoueurCollection(
            $this->joueurService->listerNotifications(request()->user())
        );
    }

    public function marquerNotificationCommeLue(Notification $notification): JsonResponse
    {
        try {
            $notification = $this->joueurService->marquerNotificationCommeLue(request()->user(), $notification);

            return response()->json([
                'status' => true,
                'message' => 'Notification marquee comme lue avec succes.',
                'data' => [
                    'notification' => [
                        'id' => $notification->id,
                        'est_lue' => $notification->est_lue,
                        'date_lecture' => $notification->date_lecture,
                    ],
                ],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        }
    }

    public function marquerToutesNotificationsCommeLues(): JsonResponse
    {
        $total = $this->joueurService->marquerToutesNotificationsCommeLues(request()->user());

        return response()->json([
            'status' => true,
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
            'data' => [
                'notifications_mises_a_jour_total' => $total,
            ],
        ]);
    }

    public function statistiques(): StatistiqueJoueurCollection
    {
        return new StatistiqueJoueurCollection(
            $this->joueurService->listerStatistiques(request()->user())
        );
    }
}
