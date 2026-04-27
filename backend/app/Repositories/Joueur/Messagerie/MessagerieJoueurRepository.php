<?php

namespace App\Repositories\Joueur\Messagerie;

use App\Events\MessageEquipeEnvoye;
use App\Models\Canal;
use App\Models\Message;
use App\Models\User;

class MessagerieJoueurRepository
{
    public function listerCanaux(User $utilisateur)
    {
        return $utilisateur->canaux()
            ->with(['equipe.club', 'utilisateurs'])
            ->latest()
            ->get();
    }

    public function utilisateurAppartientAuCanal(User $utilisateur, Canal $canal): bool
    {
        return $canal->utilisateurs()
            ->where('users.id', $utilisateur->id)
            ->exists();
    }

    public function listerMessagesParCanal(Canal $canal)
    {
        return $canal->messages()
            ->with('expediteur')
            ->oldest()
            ->get();
    }

    public function creerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $message = Message::create([
            'canal_id' => $canal->id,
            'equipe_id' => $canal->equipe_id,
            'expediteur_id' => $utilisateur->id,
            'contenu' => $donnees['contenu'],
            'type_message' => 'equipe',
        ])->fresh(['expediteur', 'equipe.club', 'canal']);

        event(new MessageEquipeEnvoye($message));

        return $message;
    }

    public function mettreAJourMessage(Message $message, array $donnees): Message
    {
        $message->update([
            'contenu' => $donnees['contenu'],
        ]);

        return $message->fresh(['expediteur', 'equipe.club', 'canal']);
    }

    public function supprimerMessage(Message $message): void
    {
        $message->delete();
    }

    public function listerCanauxRecents(User $utilisateur, int $limite = 3)
    {
        return $utilisateur->canaux()
            ->with(['equipe.club', 'utilisateurs'])
            ->latest()
            ->limit($limite)
            ->get();
    }
}
