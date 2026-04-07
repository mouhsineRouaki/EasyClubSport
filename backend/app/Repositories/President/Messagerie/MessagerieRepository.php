<?php

namespace App\Repositories\President\Messagerie;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MessagerieRepository
{
    public function listerCanauxParPresident(User $utilisateur): Collection
    {
        return Canal::with(['equipe.club', 'utilisateurs'])
            ->whereHas('equipe.club', function ($query) use ($utilisateur) {
                $query->where('president_id', $utilisateur->id);
            })
            ->latest()
            ->get();
    }

    public function listerCanauxParEquipe(Equipe $equipe): Collection
    {
        return Canal::with(['equipe.club', 'utilisateurs'])
            ->where('equipe_id', $equipe->id)
            ->latest()
            ->get();
    }

    public function trouverCanalEquipe(Equipe $equipe): ?Canal
    {
        return Canal::where('equipe_id', $equipe->id)
            ->where('type_canal', 'equipe')
            ->first();
    }

    public function creerCanal(array $donnees): Canal
    {
        return Canal::create($donnees)->load(['equipe.club', 'utilisateurs']);
    }

    public function attacherUtilisateurs(Canal $canal, array $utilisateurIds): void
    {
        $canal->utilisateurs()->syncWithoutDetaching($utilisateurIds);
    }

    public function listerMessagesParCanal(Canal $canal): Collection
    {
        return Message::with(['expediteur', 'equipe.club'])
            ->where('equipe_id', $canal->equipe_id)
            ->where('type_message', 'equipe')
            ->oldest()
            ->get();
    }

    public function creerMessage(array $donnees): Message
    {
        return Message::create($donnees)->load(['expediteur', 'equipe.club']);
    }

    public function mettreAJourMessage(Message $message, array $donnees): Message
    {
        $message->update($donnees);

        return $message->fresh(['expediteur', 'equipe.club']);
    }

    public function supprimerMessage(Message $message): void
    {
        $message->delete();
    }
}
