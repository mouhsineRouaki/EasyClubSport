<?php

namespace App\Services\Coach\Evenement;

use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use App\Repositories\Coach\Equipe\EquipeCoachRepository;
use App\Repositories\Coach\Evenement\EvenementCoachRepository;
use App\Services\Evenement\MatchInvitationService;
use App\Services\Notification\NotificationService;
use App\Support\CompositionMatchPresenter;
use App\Support\FeuilleMatchPresenter;
use App\Support\StatistiqueMatchPresenter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class EvenementCoachService
{
    public function __construct(
        protected EvenementCoachRepository $evenementCoachRepository,
        protected EquipeCoachRepository $equipeCoachRepository,
        protected MatchInvitationService $matchInvitationService,
        protected NotificationService $notificationService
    ) {
    }

    public function listerEvenementsEquipe(User $utilisateur, Equipe $equipe)
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        return $this->evenementCoachRepository->listerEvenementsEquipe($equipe);
    }

    public function listerDisponibilitesEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement)
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);

        return $this->evenementCoachRepository->listerDisponibilitesEvenement($equipe, $evenement);
    }

    public function recupererCompositionMatch(User $utilisateur, Equipe $equipe, Evenement $evenement): array
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);

        $evenement = $this->evenementCoachRepository->recupererEvenementAvecComposition($evenement);

        return CompositionMatchPresenter::depuisEvenement($evenement) ?? [];
    }

    public function enregistrerCompositionMatch(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): array
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);
        $this->verifierMatch($evenement, 'La composition est disponible uniquement pour les matchs.');

        $joueursIds = $this->equipeCoachRepository->listerJoueursEquipe($equipe)
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

        $evenement = $this->evenementCoachRepository->enregistrerCompositionMatch($evenement, $donnees);

        return CompositionMatchPresenter::depuisEvenement($evenement) ?? [];
    }

    public function recupererFeuilleMatch(User $utilisateur, Equipe $equipe, Evenement $evenement): ?array
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);
        $this->verifierMatch($evenement, 'La feuille de match est disponible uniquement pour les matchs.');

        $evenement = $this->evenementCoachRepository->recupererEvenementAvecFeuilleMatch($evenement);

        return FeuilleMatchPresenter::depuisEvenement($evenement);
    }

    public function enregistrerFeuilleMatch(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): ?array
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);
        $this->verifierMatch($evenement, 'La feuille de match est disponible uniquement pour les matchs.');

        $evenement = $this->evenementCoachRepository->enregistrerFeuilleMatch($evenement, $donnees);
        $this->notificationService->notifierFeuilleMatchPubliee($evenement);

        return FeuilleMatchPresenter::depuisEvenement($evenement);
    }

    public function recupererStatistiquesMatch(User $utilisateur, Equipe $equipe, Evenement $evenement): array
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);
        $this->verifierMatch($evenement, 'Les statistiques sont disponibles uniquement pour les matchs.');

        $evenement = $this->evenementCoachRepository->recupererEvenementAvecStatistiques($evenement);

        return StatistiqueMatchPresenter::depuisEvenement($evenement);
    }

    public function enregistrerStatistiquesMatch(User $utilisateur, Equipe $equipe, Evenement $evenement, array $donnees): array
    {
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);
        $this->verifierMatch($evenement, 'Les statistiques sont disponibles uniquement pour les matchs.');

        $joueursIds = $this->equipeCoachRepository->listerJoueursEquipe($equipe)
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

        $evenement = $this->evenementCoachRepository->enregistrerStatistiquesMatch($evenement, $donnees);
        $this->notificationService->notifierStatistiquesMatchPubliees($evenement);

        return StatistiqueMatchPresenter::depuisEvenement($evenement);
    }

    public function creerEvenement(User $utilisateur, Equipe $equipe, array $donnees): Evenement
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        $donnees = $this->synchroniserAdversaire($donnees);

        $evenement = $this->evenementCoachRepository->creerEvenement([
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
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);

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

        $evenement = $this->evenementCoachRepository->mettreAJourEvenement($evenement, $donnees);

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
        $this->verifierAccesEvenement($utilisateur, $equipe, $evenement);

        $this->evenementCoachRepository->supprimerEvenement($evenement);
    }

    protected function verifierAccesEvenement(User $utilisateur, Equipe $equipe, Evenement $evenement): void
    {
        $this->verifierEquipeCoach($utilisateur, $equipe);

        if ((int) $evenement->equipe_id !== (int) $equipe->id) {
            throw new AuthorizationException('Cet evenement ne correspond pas a cette equipe.');
        }
    }

    protected function verifierEquipeCoach(User $utilisateur, Equipe $equipe): void
    {
        if ((int) $equipe->coach_id !== (int) $utilisateur->id) {
            throw new AuthorizationException('Vous ne pouvez gerer que vos equipes.');
        }
    }

    protected function verifierMatch(Evenement $evenement, string $message): void
    {
        if ($evenement->type !== 'match') {
            throw ValidationException::withMessages([
                'evenement' => $message,
            ]);
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
}
