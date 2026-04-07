<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function creer(User $utilisateur, Message $message): bool
    {
        return $utilisateur->role === 'president'
            && (int) $message->equipe?->club?->president_id === (int) $utilisateur->id;
    }

    public function voir(User $utilisateur, Message $message): bool
    {
        return $utilisateur->role === 'president'
            && (int) $message->equipe?->club?->president_id === (int) $utilisateur->id;
    }

    public function modifier(User $utilisateur, Message $message): bool
    {
        return $utilisateur->role === 'president'
            && (int) $message->expediteur_id === (int) $utilisateur->id
            && (int) $message->equipe?->club?->president_id === (int) $utilisateur->id;
    }

    public function supprimer(User $utilisateur, Message $message): bool
    {
        return $this->modifier($utilisateur, $message);
    }
}
