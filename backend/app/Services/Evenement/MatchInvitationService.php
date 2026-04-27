<?php

namespace App\Services\Evenement;

use App\Models\Evenement;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MatchInvitationService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function notifierDemande(Evenement $evenement): void
    {
        $evenement->loadMissing(['equipe.club', 'adversaireEquipe.club', 'adversaireEquipe.coach']);

        if (! $this->estDemandeMatchValide($evenement)) {
            return;
        }

        $dateMatch = $evenement->date_debut?->format('d/m/Y H:i') ?? 'date non definie';
        $equipeLocale = $evenement->equipe?->nom ?? 'Une equipe';
        $equipeAdverse = $evenement->adversaireEquipe?->nom ?? 'votre equipe';

        $this->destinatairesAdversaires($evenement)->each(function (User $destinataire) use ($evenement, $equipeLocale, $equipeAdverse, $dateMatch) {
            $this->notificationService->updateOrCreatePourUtilisateur(
                $destinataire,
                [
                    'evenement_id' => $evenement->id,
                    'action' => 'match_invitation',
                ],
                [
                    'evenement_id' => $evenement->id,
                    'titre' => 'Demande de match a valider',
                    'contenu' => "{$equipeLocale} souhaite jouer contre {$equipeAdverse} le {$dateMatch}.",
                    'type_notification' => 'alerte',
                    'action' => 'match_invitation',
                    'statut_action' => 'en_attente',
                    'module_cible' => 'evenements',
                    'cible_id' => $evenement->id,
                    'est_lue' => false,
                    'date_lecture' => null,
                ]
            );
        });
    }

    public function repondre(User $utilisateur, Evenement $evenement, string $decision): Evenement
    {
        $evenement->loadMissing(['equipe.club', 'equipe.coach', 'adversaireEquipe.club', 'adversaireEquipe.coach', 'createur']);

        if (! in_array($decision, ['accepte', 'refuse'], true)) {
            throw ValidationException::withMessages([
                'decision' => 'La decision doit etre accepte ou refuse.',
            ]);
        }

        if (! $this->estDemandeMatchValide($evenement)) {
            throw ValidationException::withMessages([
                'evenement' => 'Cet evenement n est pas une demande de match valide.',
            ]);
        }

        if (! $this->utilisateurPeutRepondre($utilisateur, $evenement)) {
            throw new AuthorizationException('Vous ne pouvez repondre qu aux demandes envoyees a votre equipe.');
        }

        if ($evenement->statut_invitation_adversaire !== 'en_attente') {
            throw ValidationException::withMessages([
                'invitation' => 'Cette demande a deja ete traitee.',
            ]);
        }

        $evenement->update([
            'statut_invitation_adversaire' => $decision,
            'invitation_adversaire_repondue_par_id' => $utilisateur->id,
            'invitation_adversaire_repondue_at' => now(),
            'statut' => $decision === 'refuse' ? 'annule' : $evenement->statut,
        ]);

        Notification::query()
            ->where('evenement_id', $evenement->id)
            ->where('action', 'match_invitation')
            ->update([
                'statut_action' => $decision,
                'est_lue' => true,
                'date_lecture' => now(),
            ]);

        $this->notifierReponse($evenement->fresh(['equipe.club', 'equipe.coach', 'adversaireEquipe.club', 'adversaireEquipe.coach', 'createur']), $decision, $utilisateur);

        return $evenement->fresh(['equipe.club', 'adversaireEquipe.club', 'invitationAdversaireReponduePar']);
    }

    public function utilisateurPeutRepondre(User $utilisateur, Evenement $evenement): bool
    {
        $evenement->loadMissing(['adversaireEquipe.club']);

        return (int) $evenement->adversaireEquipe?->coach_id === (int) $utilisateur->id
            || (int) $evenement->adversaireEquipe?->club?->president_id === (int) $utilisateur->id;
    }

    protected function estDemandeMatchValide(Evenement $evenement): bool
    {
        return $evenement->type === 'match' && (bool) $evenement->adversaire_equipe_id;
    }

    protected function destinatairesAdversaires(Evenement $evenement): Collection
    {
        $destinataires = collect([
            $evenement->adversaireEquipe?->coach,
            $evenement->adversaireEquipe?->club?->president,
        ])->filter();

        return $destinataires->unique('id')->values();
    }

    protected function notifierReponse(Evenement $evenement, string $decision, User $utilisateur): void
    {
        $statutLisible = $decision === 'accepte' ? 'acceptee' : 'refusee';
        $equipeAdverse = $evenement->adversaireEquipe?->nom ?? 'L equipe adverse';

        $destinataires = collect([
            $evenement->createur,
            $evenement->equipe?->coach,
            $evenement->equipe?->club?->president,
        ])
            ->filter()
            ->reject(fn (User $destinataire) => (int) $destinataire->id === (int) $utilisateur->id)
            ->unique('id')
            ->values();

        $destinataires->each(function (User $destinataire) use ($evenement, $statutLisible, $equipeAdverse) {
            $this->notificationService->creerPourUtilisateur($destinataire, [
                'evenement_id' => $evenement->id,
                'titre' => 'Reponse a la demande de match',
                'contenu' => "La demande de match contre {$equipeAdverse} a ete {$statutLisible}.",
                'type_notification' => 'info',
                'action' => 'match_invitation_response',
                'statut_action' => $evenement->statut_invitation_adversaire,
                'module_cible' => 'evenements',
                'cible_id' => $evenement->id,
            ]);
        });
    }
}
