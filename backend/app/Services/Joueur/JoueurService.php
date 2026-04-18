<?php

namespace App\Services\Joueur;

use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Evenement;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Joueur\JoueurRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class JoueurService
{
    public function __construct(
        protected JoueurRepository $joueurRepository
    ) {
    }

    public function recupererProfil(User $utilisateur): User
    {
        return $this->joueurRepository->recupererProfil($utilisateur);
    }

    public function recupererEquipe(User $utilisateur)
    {
        return $this->joueurRepository->recupererEquipeActive($utilisateur);
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees, ?UploadedFile $photo = null): array
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

        $utilisateur = $this->joueurRepository->mettreAJourProfil($utilisateur, $donnees);

        return [
            'utilisateur' => $utilisateur,
            'equipe' => $this->joueurRepository->recupererEquipeActive($utilisateur),
        ];
    }

    public function listerEvenements(User $utilisateur)
    {
        return $this->joueurRepository->listerEvenements($utilisateur);
    }

    public function repondreDisponibilite(User $utilisateur, Evenement $evenement, array $donnees)
    {
        $equipe = $this->joueurRepository->recupererEquipeActive($utilisateur);

        if (! $equipe || (int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Vous ne pouvez repondre qu aux evenements de votre equipe.');
        }

        return $this->joueurRepository->enregistrerDisponibilite($utilisateur, $evenement, $donnees);
    }

    public function listerConvocations(User $utilisateur)
    {
        return $this->joueurRepository->listerConvocations($utilisateur);
    }

    public function repondreConvocation(User $utilisateur, Convocation $convocation, array $donnees): Convocation
    {
        if ((int) $convocation->utilisateur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Cette convocation ne vous appartient pas.');
        }

        return $this->joueurRepository->mettreAJourConvocation($convocation, $donnees);
    }

    public function listerDocuments(User $utilisateur)
    {
        return $this->joueurRepository->listerDocuments($utilisateur);
    }

    public function listerCanaux(User $utilisateur)
    {
        return $this->joueurRepository->listerCanaux($utilisateur);
    }

    public function listerMessages(Canal $canal, User $utilisateur)
    {
        $this->verifierAccesCanal($utilisateur, $canal);

        return $this->joueurRepository->listerMessagesParCanal($canal);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $this->verifierAccesCanal($utilisateur, $canal);

        return $this->joueurRepository->creerMessage($utilisateur, $canal, $donnees);
    }

    public function modifierMessage(User $utilisateur, Message $message, array $donnees): Message
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez modifier que vos propres messages.');
        }

        return $this->joueurRepository->mettreAJourMessage($message, $donnees);
    }

    public function supprimerMessage(User $utilisateur, Message $message): void
    {
        if ((int) $message->expediteur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez supprimer que vos propres messages.');
        }

        $this->joueurRepository->supprimerMessage($message);
    }

    public function listerNotifications(User $utilisateur)
    {
        return $this->joueurRepository->listerNotifications($utilisateur);
    }

    public function marquerNotificationCommeLue(User $utilisateur, Notification $notification): Notification
    {
        if ((int) $notification->utilisateur_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Cette notification ne vous appartient pas.');
        }

        return $this->joueurRepository->marquerNotificationCommeLue($notification);
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return $this->joueurRepository->marquerToutesNotificationsCommeLues($utilisateur);
    }

    public function listerStatistiques(User $utilisateur)
    {
        return $this->joueurRepository->listerStatistiques($utilisateur);
    }

    public function recupererDashboard(User $utilisateur): array
    {
        $equipe = $this->joueurRepository->recupererEquipeActive($utilisateur);

        return [
            'equipe' => $equipe,
            'prochain_evenement' => $this->joueurRepository->prochainEvenement($utilisateur),
            'convocations_en_attente_total' => $this->joueurRepository->compterConvocationsEnAttente($utilisateur),
            'notifications_non_lues_total' => $this->joueurRepository->compterNotificationsNonLues($utilisateur),
            'evenements' => $this->joueurRepository->listerEvenementsRecentsEquipe($utilisateur),
            'convocations' => $this->joueurRepository->listerConvocationsRecentes($utilisateur),
            'dernieres_annonces' => $this->joueurRepository->listerAnnoncesEquipe($utilisateur),
            'derniers_documents' => $this->joueurRepository->listerDocumentsRecents($utilisateur),
            'derniers_canaux' => $this->joueurRepository->listerCanauxRecents($utilisateur),
        ];
    }

    public function rejoindreEquipeParCode(User $utilisateur, string $codeInvitation)
    {
        if ($this->joueurRepository->utilisateurAPourEquipeActive($utilisateur)) {
            throw ValidationException::withMessages([
                'code_invitation' => 'Vous etes deja assigne a une equipe.',
            ]);
        }

        $equipe = $this->joueurRepository->trouverEquipeParCodeInvitation($codeInvitation);

        if (! $equipe) {
            throw ValidationException::withMessages([
                'code_invitation' => 'Code d invitation invalide.',
            ]);
        }

        if ($equipe->statut !== 'active') {
            throw ValidationException::withMessages([
                'code_invitation' => 'Cette equipe n accepte pas de nouvelles adhesions.',
            ]);
        }

        $this->joueurRepository->rattacherJoueurAEquipe($utilisateur, $equipe);

        return $this->joueurRepository->recupererEquipeActive($utilisateur);
    }

    protected function verifierAccesCanal(User $utilisateur, Canal $canal): void
    {
        if (! $this->joueurRepository->utilisateurAppartientAuCanal($utilisateur, $canal)) {
            throw new AuthorizationException('Vous n avez pas acces a ce canal.');
        }
    }
}
