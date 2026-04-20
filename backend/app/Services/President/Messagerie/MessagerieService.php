<?php

namespace App\Services\President\Messagerie;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\Message;
use App\Models\User;
use App\Repositories\President\Messagerie\MessagerieRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MessagerieService
{
    public function __construct(
        protected MessagerieRepository $messagerieRepository,
        protected NotificationService $notificationService
    ) {
    }

    public function listerCanaux(User $utilisateur, array $filtres = [])
    {
        return $this->messagerieRepository->listerCanauxParPresident($utilisateur, $filtres);
    }

    public function listerCanauxParEquipe(Equipe $equipe, array $filtres = [])
    {
        return $this->messagerieRepository->listerCanauxParEquipe($equipe, $filtres);
    }

    public function creerCanal(User $utilisateur, Equipe $equipe, array $donnees): Canal
    {
        $typeCanal = $donnees['type_canal'] ?? 'equipe';

        if ($typeCanal === 'equipe' && $this->messagerieRepository->trouverCanalEquipe($equipe)) {
            throw ValidationException::withMessages([
                'equipe' => 'Un canal existe deja pour cette equipe.',
            ]);
        }

        $participantsSelectionnes = collect($donnees['utilisateur_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($typeCanal === 'prive' && $participantsSelectionnes->isEmpty()) {
            throw ValidationException::withMessages([
                'utilisateur_ids' => 'Selectionnez au moins une personne pour commencer la conversation.',
            ]);
        }

        $participantsDisponibles = $this->messagerieRepository
            ->listerParticipantsEquipe($equipe)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($typeCanal === 'prive' && $participantsSelectionnes->diff($participantsDisponibles)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'utilisateur_ids' => 'Tous les participants doivent appartenir a cette equipe.',
            ]);
        }

        $nomCanal = $donnees['nom'] ?? null;
        if (! $nomCanal) {
            if ($typeCanal === 'prive') {
                $participantsNoms = $this->messagerieRepository
                    ->listerParticipantsEquipe($equipe)
                    ->whereIn('id', $participantsSelectionnes->all())
                    ->take(3)
                    ->map(fn ($utilisateur) => trim(($utilisateur['prenom'] ?? '').' '.($utilisateur['nom'] ?? '')) ?: ($utilisateur['name'] ?? 'Participant'))
                    ->implode(', ');

                $nomCanal = $participantsNoms ? "Conversation - {$participantsNoms}" : "Conversation - {$equipe->nom}";
            } else {
                $nomCanal = "Canal - {$equipe->nom}";
            }
        }

        $canal = $this->messagerieRepository->creerCanal([
            'equipe_id' => $equipe->id,
            'nom' => $nomCanal,
            'type_canal' => $typeCanal,
            'description' => $donnees['description'] ?? null,
        ]);

        $utilisateurIds = $typeCanal === 'prive'
            ? collect([$utilisateur->id])->merge($participantsSelectionnes)->filter()->unique()->values()->all()
            : collect([$utilisateur->id, $equipe->coach_id])
                ->merge($equipe->utilisateurs()->pluck('users.id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

        $this->messagerieRepository->attacherUtilisateurs($canal, $utilisateurIds);

        return $canal->fresh(['equipe.club', 'utilisateurs']);
    }

    public function listerParticipantsEquipe(Equipe $equipe, string $recherche = ''): Collection
    {
        return $this->messagerieRepository->listerParticipantsEquipe($equipe, $recherche);
    }

    public function listerMessages(Canal $canal, array $filtres = [])
    {
        return $this->messagerieRepository->listerMessagesParCanal($canal, $filtres);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $message = $this->messagerieRepository->creerMessage([
            'canal_id' => $canal->id,
            'equipe_id' => $canal->equipe_id,
            'expediteur_id' => $utilisateur->id,
            'contenu' => $donnees['contenu'],
            'type_message' => 'equipe',
        ]);

        $this->notificationService->notifierNouveauMessage($message);

        return $message;
    }

    public function modifierMessage(Message $message, array $donnees): Message
    {
        return $this->messagerieRepository->mettreAJourMessage($message, [
            'contenu' => $donnees['contenu'],
        ]);
    }

    public function supprimerMessage(Message $message): void
    {
        $this->messagerieRepository->supprimerMessage($message);
    }
}
