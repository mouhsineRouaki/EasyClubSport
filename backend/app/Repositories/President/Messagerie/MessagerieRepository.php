<?php

namespace App\Repositories\President\Messagerie;

use App\Events\MessageEquipeEnvoye;
use App\Models\Canal;
use App\Models\Equipe;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessagerieRepository
{
    public function listerCanauxParPresident(User $utilisateur, array $filtres = []): LengthAwarePaginator
    {
        $query = Canal::with(['equipe.club', 'utilisateurs'])
            ->whereHas('equipe.club', function ($query) use ($utilisateur) {
                $query->where('president_id', $utilisateur->id);
            });

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('description', 'like', "%{$terme}%");
            });
        }

        return $query
            ->latest()
            ->paginate(
                (int) ($filtres['per_page'] ?? 12),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function listerCanauxParEquipe(Equipe $equipe, array $filtres = []): LengthAwarePaginator
    {
        $query = Canal::with(['equipe.club', 'utilisateurs'])
            ->where('equipe_id', $equipe->id);

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('description', 'like', "%{$terme}%");
            });
        }

        return $query
            ->latest()
            ->paginate(
                (int) ($filtres['per_page'] ?? 12),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
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

    public function listerMessagesParCanal(Canal $canal, array $filtres = []): LengthAwarePaginator
    {
        $query = Message::with(['expediteur', 'equipe.club'])
            ->where('equipe_id', $canal->equipe_id)
            ->where('type_message', 'equipe');

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('contenu', 'like', "%{$terme}%")
                    ->orWhereHas('expediteur', function ($userQuery) use ($terme) {
                        $userQuery->where('nom', 'like', "%{$terme}%")
                            ->orWhere('prenom', 'like', "%{$terme}%")
                            ->orWhere('email', 'like', "%{$terme}%");
                    });
            });
        }

        return $query->latest()
            ->paginate(
                (int) ($filtres['per_page'] ?? 20),
                ['*'],
                'page',
                (int) ($filtres['page'] ?? 1)
            );
    }

    public function creerMessage(array $donnees): Message
    {
        $message = Message::create($donnees)->load(['expediteur', 'equipe.club']);

        event(new MessageEquipeEnvoye($message));

        return $message;
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
