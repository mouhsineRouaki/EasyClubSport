<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isPresident()
            || $utilisateur->isCoach()
            || $utilisateur->isJoueur();
    }

    public function creer(User $utilisateur, Message $message): bool
    {
        return $this->voir($utilisateur, $message);
    }

    public function voir(User $utilisateur, Message $message): bool
    {
        return $utilisateur->presidesClub($message->equipe?->club)
            || $utilisateur->coachesEquipe($message->equipe)
            || $utilisateur->belongsToCanal($message->canal);
    }

    public function modifier(User $utilisateur, Message $message): bool
    {
        return (int) $message->expediteur_id === (int) $utilisateur->id
            && $this->voir($utilisateur, $message);
    }

    public function supprimer(User $utilisateur, Message $message): bool
    {
        return $this->modifier($utilisateur, $message);
    }
}
