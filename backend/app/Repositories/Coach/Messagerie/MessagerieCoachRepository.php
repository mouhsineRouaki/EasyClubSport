<?php

namespace App\Repositories\Coach\Messagerie;

use App\Events\MessageEquipeEnvoye;
use App\Models\Canal;
use App\Models\Message;
use App\Models\User;

class MessagerieCoachRepository
{
    public function listerCanaux(User $utilisateur)
    {
        return Canal::query()
            ->whereHas('equipe', function ($query) use ($utilisateur) {
                $query->where('coach_id', $utilisateur->id);
            })
            ->with(['equipe.club', 'utilisateurs'])
            ->latest()
            ->get();
    }

    public function coachPeutVoirCanal(User $utilisateur, Canal $canal): bool
    {
        return (int) $canal->equipe?->coach_id === (int) $utilisateur->id;
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
}
