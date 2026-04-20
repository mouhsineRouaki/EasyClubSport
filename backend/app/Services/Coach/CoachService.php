<?php

namespace App\Services\Coach;

use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Coach\CoachRepository;
use App\Support\CompositionMatchPresenter;
use App\Support\FeuilleMatchPresenter;
use App\Support\StatistiqueMatchPresenter;
use App\Services\Evenement\MatchInvitationService;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CoachService
{
    public function __construct(
        protected CoachRepository $coachRepository,
        protected MatchInvitationService $matchInvitationService,
        protected NotificationService $notificationService
    ) {
    }

    public function recupererProfil(User $utilisateur): User
    {
        return $this->coachRepository->recupererProfil($utilisateur);
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees, ?UploadedFile $photo = null): User
    {
        if (isset($donnees['nom']) || isset($donnees['prenom'])) {
            $nom = $donnees['nom'] ?? $utilisateur->nom;
            $prenom = $donnees['prenom'] ?? $utilisateur->prenom;
            $donnees['name'] = trim($prenom.' '.$nom);
        }

        if ($photo) {
            if ($utilisateur->photo) {
                Storage::disk('public')->delete($utilisateur->photo);
            }

            $donnees['photo'] = $photo->store('profils', 'public');
        }

        return $this->coachRepository->mettreAJourProfil($utilisateur, $donnees);
    }

    public function listerEquipes(User $utilisateur)
    {
        return $this->coachRepository->listerEquipesCoach($utilisateur);
    }

    public function listerJoueursEquipe(User $utilisateur, Equipe $equipe)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->coachRepository->listerJoueursEquipe($equipe);
    }

    public function listerEvenementsEquipe(User $utilisateur, Equipe $equipe)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->coachRepository->listerEvenementsEquipe($equipe);
    }

    public function listerDisponibilitesEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        return $this->coachRepository->listerDisponibilitesEvenement($equipe, $evenement);
    }

    public function recupererCompositionMatch(User $utilisateur, Equipe $equipe, Evenement $evenement): array
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        $evenement = $this->coachRepository->recupererEvenementAvecComposition($evenement);

        return CompositionMatchPresenter::depuisEvenement($evenement) ?? [];
    }

    public function enregistrerCompositionMatch(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): array
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        if ($evenement->type !== 'match') {
            throw ValidationException::withMessages([
                'evenement' => 'La composition est disponible uniquement pour les matchs.',
            ]);
        }

        $joueursIds = $this->coachRepository->listerJoueursEquipe($equipe)
            ->pluck('utilisateur_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $titulaires = collect($donnees['titulaires'] ?? []);
        $remplacants = collect($donnees['remplacants'] ?? []);

        $joueursSelectionnes = $titulaires
            ->pluck('utilisateur_id')
            ->concat($remplacants->pluck('utilisateur_id'))
            ->map(fn ($id) => (int) $id)
            ->values();

        foreach ($joueursSelectionnes as $utilisateurId) {
            if (! in_array($utilisateurId, $joueursIds, true)) {
                throw ValidationException::withMessages([
                    'composition' => 'Tous les joueurs de la composition doivent appartenir a cette equipe.',
                ]);
            }
        }

        if ($joueursSelectionnes->count() !== $joueursSelectionnes->unique()->count()) {
            throw ValidationException::withMessages([
                'composition' => 'Un joueur ne peut apparaitre qu une seule fois dans la composition.',
            ]);
        }

        $evenement = $this->coachRepository->enregistrerCompositionMatch($evenement, $donnees);

        return CompositionMatchPresenter::depuisEvenement($evenement) ?? [];
    }

    public function recupererFeuilleMatch(User $utilisateur, Equipe $equipe, Evenement $evenement): ?array
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        if ($evenement->type !== 'match') {
            throw ValidationException::withMessages([
                'evenement' => 'La feuille de match est disponible uniquement pour les matchs.',
            ]);
        }

        $evenement = $this->coachRepository->recupererEvenementAvecFeuilleMatch($evenement);

        return FeuilleMatchPresenter::depuisEvenement($evenement);
    }

    public function enregistrerFeuilleMatch(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): ?array
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        if ($evenement->type !== 'match') {
            throw ValidationException::withMessages([
                'evenement' => 'La feuille de match est disponible uniquement pour les matchs.',
            ]);
        }

        $evenement = $this->coachRepository->enregistrerFeuilleMatch($evenement, $donnees);

        return FeuilleMatchPresenter::depuisEvenement($evenement);
    }

    public function recupererStatistiquesMatch(User $utilisateur, Equipe $equipe, Evenement $evenement): array
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        if ($evenement->type !== 'match') {
            throw ValidationException::withMessages([
                'evenement' => 'Les statistiques sont disponibles uniquement pour les matchs.',
            ]);
        }

        $evenement = $this->coachRepository->recupererEvenementAvecStatistiques($evenement);

        return StatistiqueMatchPresenter::depuisEvenement($evenement);
    }

    public function enregistrerStatistiquesMatch(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): array
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        if ($evenement->type !== 'match') {
            throw ValidationException::withMessages([
                'evenement' => 'Les statistiques sont disponibles uniquement pour les matchs.',
            ]);
        }

        $joueursIds = $this->coachRepository->listerJoueursEquipe($equipe)
            ->pluck('utilisateur_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach (($donnees['joueurs'] ?? []) as $ligne) {
            if (! in_array((int) $ligne['utilisateur_id'], $joueursIds, true)) {
                throw ValidationException::withMessages([
                    'joueurs' => 'Tous les joueurs statistiques doivent appartenir a cette equipe.',
                ]);
            }
        }

        $evenement = $this->coachRepository->enregistrerStatistiquesMatch($evenement, $donnees);

        return StatistiqueMatchPresenter::depuisEvenement($evenement);
    }

    public function creerEvenement(User $utilisateur, Equipe $equipe, array $donnees): Evenement
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        $donnees = $this->synchroniserAdversaire($donnees);

        $evenement = $this->coachRepository->creerEvenement([
            'equipe_id' => $equipe->id,
            'createur_id' => $utilisateur->id,
            'titre' => $donnees['titre'],
            'type' => $donnees['type'],
            'date_debut' => $donnees['date_debut'],
            'date_fin' => $donnees['date_fin'] ?? null,
            'lieu' => $donnees['lieu'] ?? null,
            'adversaire' => $donnees['adversaire'] ?? null,
            'adversaire_equipe_id' => $donnees['adversaire_equipe_id'] ?? null,
            'description' => $donnees['description'] ?? null,
            'statut' => $donnees['statut'] ?? 'planifie',
            'statut_invitation_adversaire' => $donnees['type'] === 'match' ? 'en_attente' : 'sans_invitation',
            'invitation_adversaire_repondue_par_id' => null,
            'invitation_adversaire_repondue_at' => null,
        ]);

        $this->matchInvitationService->notifierDemande($evenement);

        return $evenement;
    }

    public function modifierEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): Evenement
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        $ancienType = $evenement->type;
        $ancienAdversaireEquipeId = $evenement->adversaire_equipe_id;
        $donnees = $this->synchroniserAdversaire($donnees, $evenement);
        $invitationChangee = $this->invitationAdversaireChangee($donnees, $ancienType, $ancienAdversaireEquipeId);

        if (($donnees['type'] ?? $evenement->type) !== 'match') {
            $donnees['statut_invitation_adversaire'] = 'sans_invitation';
            $donnees['invitation_adversaire_repondue_par_id'] = null;
            $donnees['invitation_adversaire_repondue_at'] = null;
        } elseif ($invitationChangee) {
            $donnees['statut_invitation_adversaire'] = 'en_attente';
            $donnees['invitation_adversaire_repondue_par_id'] = null;
            $donnees['invitation_adversaire_repondue_at'] = null;
        }

        $evenement = $this->coachRepository->mettreAJourEvenement(
            $evenement,
            $donnees
        );

        if ($invitationChangee) {
            $this->matchInvitationService->notifierDemande($evenement);
        }

        return $evenement;
    }

    public function repondreInvitationAdversaire(User $utilisateur, Evenement $evenement, string $decision): Evenement
    {
        return $this->matchInvitationService->repondre($utilisateur, $evenement, $decision);
    }

    public function supprimerEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement): void
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        $this->coachRepository->supprimerEvenement($evenement);
    }

    public function listerConvocationsEquipe(User $utilisateur, Equipe $equipe)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->coachRepository->listerConvocationsEquipe($equipe);
    }

    public function creerConvocations(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        $joueursIds = $this->coachRepository->listerJoueursEquipe($equipe)
            ->pluck('utilisateur_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($donnees['utilisateur_ids'] as $utilisateurId) {
            if (! in_array((int) $utilisateurId, $joueursIds, true)) {
                throw ValidationException::withMessages([
                    'utilisateur_ids' => 'Tous les utilisateurs convoques doivent appartenir a cette equipe.',
                ]);
            }
        }

        $statut = $donnees['statut'] ?? 'convoque';

        $convocations = collect($donnees['utilisateur_ids'])
            ->map(fn ($utilisateurId) => $this->coachRepository->creerOuMettreAJourConvocation($evenement, (int) $utilisateurId, $statut))
            ->values();

        $this->notificationService->notifierConvocationsCrees($evenement, $convocations);

        return $convocations;
    }

    public function modifierConvocation(User $utilisateur, Convocation $convocation, array $donnees): Convocation
    {
        if ((int) $convocation->evenement?->equipe?->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que les convocations de vos equipes.');
        }

        return $this->coachRepository->mettreAJourConvocation($convocation, $donnees);
    }

    public function listerCanaux(User $utilisateur)
    {
        return $this->coachRepository->listerCanaux($utilisateur);
    }

    public function listerMessages(User $utilisateur, Canal $canal)
    {
        $this->verifierCanalCoach($utilisateur, $canal);

        return $this->coachRepository->listerMessagesParCanal($canal);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $this->verifierCanalCoach($utilisateur, $canal);

        $message = $this->coachRepository->creerMessage($utilisateur, $canal, $donnees);
        $this->notificationService->notifierNouveauMessage($message);

        return $message;
    }

    public function modifierMessage(User $utilisateur, Message $message, array $donnees): Message
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que vos propres messages.');
        }

        if ((int) $message->equipe?->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que les messages de vos equipes.');
        }

        return $this->coachRepository->mettreAJourMessage($message, $donnees);
    }

    public function supprimerMessage(User $utilisateur, Message $message): void
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez supprimer que vos propres messages.');
        }

        if ((int) $message->equipe?->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez supprimer que les messages de vos equipes.');
        }

        $this->coachRepository->supprimerMessage($message);
    }

    public function listerNotifications(User $utilisateur)
    {
        return $this->notificationService->listerPourUtilisateur($utilisateur);
    }

    public function marquerNotificationCommeLue(User $utilisateur, Notification $notification): Notification
    {
        if ((int) $notification->utilisateur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Cette notification ne vous appartient pas.');
        }

        return $this->notificationService->marquerCommeLue($notification);
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return $this->notificationService->marquerToutesCommeLues($utilisateur);
    }

    public function recupererDashboard(User $utilisateur): array
    {
        $equipe = $this->coachRepository->recupererEquipeActiveCoach($utilisateur);

        return [
            'equipe' => $equipe,
            'equipes_total' => $this->coachRepository->compterEquipes($utilisateur),
            'joueurs_total' => $this->coachRepository->compterJoueurs($utilisateur),
            'evenements_a_venir_total' => $this->coachRepository->compterEvenementsAVenir($utilisateur),
            'convocations_en_attente_total' => $this->coachRepository->compterConvocationsEnAttente($utilisateur),
            'prochain_evenement' => $this->coachRepository->prochainEvenement($utilisateur),
            'evenements_recents' => $this->coachRepository->listerEvenementsRecents($utilisateur),
            'canaux_recents' => $this->coachRepository->listerCanauxRecents($utilisateur),
        ];
    }

    protected function verifierEquipeCoach(User $utilisateur, Equipe $equipe): void
    {
        if ((int) $equipe->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez gerer que vos equipes.');
        }
    }

    protected function synchroniserAdversaire(array $donnees, ?Evenement $evenement = null): array
    {
        $type = $donnees['type'] ?? $evenement?->type;

        if ($type !== 'match') {
            $donnees['adversaire'] = null;
            $donnees['adversaire_equipe_id'] = null;

            return $donnees;
        }

        $adversaireEquipeId = $donnees['adversaire_equipe_id'] ?? $evenement?->adversaire_equipe_id;

        if ($adversaireEquipeId) {
            $donnees['adversaire'] = Equipe::query()
                ->whereKey($adversaireEquipeId)
                ->value('nom');
        }

        return $donnees;
    }

    protected function invitationAdversaireChangee(array $donnees, ?string $ancienType, ?int $ancienAdversaireEquipeId): bool
    {
        $nouveauType = $donnees['type'] ?? $ancienType;
        $nouvelAdversaireEquipeId = array_key_exists('adversaire_equipe_id', $donnees)
            ? $donnees['adversaire_equipe_id']
            : $ancienAdversaireEquipeId;

        if ($nouveauType !== 'match') {
            return $ancienType === 'match';
        }

        return $ancienType !== 'match' || (int) $nouvelAdversaireEquipeId !== (int) $ancienAdversaireEquipeId;
    }

    protected function verifierCanalCoach(User $utilisateur, Canal $canal): void
    {
        if (! $this->coachRepository->coachPeutVoirCanal($utilisateur, $canal)) {
            throw new AuthorizationException('Vous n avez pas acces a ce canal.');
        }
    }
}
