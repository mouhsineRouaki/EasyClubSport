<?php

namespace App\Services\President\Messagerie;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\Message;
use App\Models\User;
use App\Repositories\President\Messagerie\MessagerieRepository;
use Illuminate\Validation\ValidationException;

class MessagerieService
{
    public function __construct(
        protected MessagerieRepository $messagerieRepository
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
        if ($this->messagerieRepository->trouverCanalEquipe($equipe)) {
            throw ValidationException::withMessages([
                'equipe' => 'Un canal existe deja pour cette equipe.',
            ]);
        }

        $canal = $this->messagerieRepository->creerCanal([
            'equipe_id' => $equipe->id,
            'nom' => $donnees['nom'],
            'type_canal' => $donnees['type_canal'] ?? 'equipe',
            'description' => $donnees['description'] ?? null,
        ]);

        $utilisateurIds = collect([$utilisateur->id, $equipe->coach_id])
            ->merge($equipe->utilisateurs()->pluck('users.id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->messagerieRepository->attacherUtilisateurs($canal, $utilisateurIds);

        return $canal->fresh(['equipe.club', 'utilisateurs']);
    }

    public function listerMessages(Canal $canal, array $filtres = [])
    {
        return $this->messagerieRepository->listerMessagesParCanal($canal, $filtres);
    }

    public function envoyerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        return $this->messagerieRepository->creerMessage([
            'equipe_id' => $canal->equipe_id,
            'expediteur_id' => $utilisateur->id,
            'contenu' => $donnees['contenu'],
            'type_message' => 'equipe',
        ]);
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
