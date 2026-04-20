<?php

namespace App\Repositories\President\Messagerie;

use App\Events\MessageEquipeEnvoye;
use App\Models\Canal;
use App\Models\Equipe;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;
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

    public function listerParticipantsEquipe(Equipe $equipe, string $recherche = ''): Collection
    {
        $joueurs = $equipe->utilisateurs()
            ->select('users.*', 'membre_equipes.role_equipe', 'membre_equipes.date_affectation')
            ->when($recherche, function ($query) use ($recherche) {
                $query->where(function ($subQuery) use ($recherche) {
                    $subQuery->where('users.nom', 'like', "%{$recherche}%")
                        ->orWhere('users.prenom', 'like', "%{$recherche}%")
                        ->orWhere('users.email', 'like', "%{$recherche}%");
                });
            })
            ->orderBy('users.nom')
            ->orderBy('users.prenom')
            ->get()
            ->map(function ($utilisateur) {
                return [
                    'id' => $utilisateur->id,
                    'name' => $utilisateur->name,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'email' => $utilisateur->email,
                    'telephone' => $utilisateur->telephone,
                    'photo' => $utilisateur->photo,
                    'photo_url' => $utilisateur->photo ? asset('storage/'.$utilisateur->photo) : null,
                    'role' => $utilisateur->role,
                    'role_equipe' => $utilisateur->role_equipe ?? $utilisateur->pivot?->role_equipe,
                    'date_affectation' => $utilisateur->date_affectation ?? $utilisateur->pivot?->date_affectation,
                ];
            });

        $coach = $equipe->coach;
        if ($coach) {
            $nomComplet = trim(($coach->prenom ?? '').' '.($coach->nom ?? ''));
            $correspond = ! $recherche
                || str_contains(mb_strtolower($nomComplet), mb_strtolower($recherche))
                || str_contains(mb_strtolower((string) $coach->email), mb_strtolower($recherche));

            if ($correspond && ! $joueurs->contains(fn ($utilisateur) => (int) $utilisateur['id'] === (int) $coach->id)) {
                $joueurs->prepend([
                    'id' => $coach->id,
                    'name' => $coach->name,
                    'nom' => $coach->nom,
                    'prenom' => $coach->prenom,
                    'email' => $coach->email,
                    'telephone' => $coach->telephone,
                    'photo' => $coach->photo,
                    'photo_url' => $coach->photo ? asset('storage/'.$coach->photo) : null,
                    'role' => $coach->role,
                    'role_equipe' => 'coach',
                    'date_affectation' => null,
                ]);
            }
        }

        return $joueurs->values();
    }

    public function listerMessagesParCanal(Canal $canal, array $filtres = []): LengthAwarePaginator
    {
        $query = Message::with(['expediteur', 'equipe.club', 'canal'])
            ->where('canal_id', $canal->id)
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
        $message = Message::create($donnees)->load(['expediteur', 'equipe.club', 'canal']);

        event(new MessageEquipeEnvoye($message));

        return $message;
    }

    public function mettreAJourMessage(Message $message, array $donnees): Message
    {
        $message->update($donnees);

        return $message->fresh(['expediteur', 'equipe.club', 'canal']);
    }

    public function supprimerMessage(Message $message): void
    {
        $message->delete();
    }
}
