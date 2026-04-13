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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CoachService
{
    public function __construct(
        protected CoachRepository $coachRepository
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

    public function creerEvenement(User $utilisateur, Equipe $equipe, array $donnees): Evenement
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->coachRepository->creerEvenement([
            'equipe_id' => $equipe->id,
            'createur_id' => $utilisateur->id,
            'titre' => $donnees['titre'],
            'type' => $donnees['type'],
            'date_debut' => $donnees['date_debut'],
            'date_fin' => $donnees['date_fin'] ?? null,
            'lieu' => $donnees['lieu'] ?? null,
            'adversaire' => $donnees['adversaire'] ?? null,
            'description' => $donnees['description'] ?? null,
            'statut' => $donnees['statut'] ?? 'planifie',
        ]);
    }

    public function modifierEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): Evenement
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }

        return $this->coachRepository->mettreAJourEvenement($evenement, $donnees);
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

        return collect($donnees['utilisateur_ids'])
            ->map(fn ($utilisateurId) => $this->coachRepository->creerOuMettreAJourConvocation($evenement, (int) $utilisateurId, $statut))
            ->values();
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

        return $this->coachRepository->creerMessage($utilisateur, $canal, $donnees);
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
        return $this->coachRepository->listerNotifications($utilisateur);
    }

    public function marquerNotificationCommeLue(User $utilisateur, Notification $notification): Notification
    {
        if ((int) $notification->utilisateur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Cette notification ne vous appartient pas.');
        }

        return $this->coachRepository->marquerNotificationCommeLue($notification);
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return $this->coachRepository->marquerToutesNotificationsCommeLues($utilisateur);
    }

    public function recupererDashboard(User $utilisateur): array
    {
        return [
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

    protected function verifierCanalCoach(User $utilisateur, Canal $canal): void
    {
        if (! $this->coachRepository->coachPeutVoirCanal($utilisateur, $canal)) {
            throw new AuthorizationException('Vous n avez pas acces a ce canal.');
        }
    }
}