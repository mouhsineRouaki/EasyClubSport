<?php

namespace App\Repositories\President\Document;

use App\Models\Club;
use App\Models\Document;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DocumentRepository
{
    public function listerParPresident(User $utilisateur, array $filtres = []): LengthAwarePaginator
    {
        $query = Document::with('utilisateur')
            ->whereHas('utilisateur', function ($query) use ($utilisateur) {
                $query->whereHas('clubsPresides', function ($subQuery) use ($utilisateur) {
                    $subQuery->where('president_id', $utilisateur->id);
                })->orWhereHas('equipesCoachees', function ($subQuery) use ($utilisateur) {
                    $subQuery->whereHas('club', function ($clubQuery) use ($utilisateur) {
                        $clubQuery->where('president_id', $utilisateur->id);
                    });
                })->orWhereHas('membreEquipes', function ($subQuery) use ($utilisateur) {
                    $subQuery->whereHas('equipe.club', function ($clubQuery) use ($utilisateur) {
                        $clubQuery->where('president_id', $utilisateur->id);
                    });
                });
            });

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('type_document', 'like', "%{$terme}%")
                    ->orWhereHas('utilisateur', function ($userQuery) use ($terme) {
                        $userQuery->where('nom', 'like', "%{$terme}%")
                            ->orWhere('prenom', 'like', "%{$terme}%")
                            ->orWhere('email', 'like', "%{$terme}%");
                    });
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

    public function listerParClub(Club $club, array $filtres = []): LengthAwarePaginator
    {
        $query = Document::with('utilisateur')
            ->whereHas('utilisateur', function ($query) use ($club) {
                $query->where(function ($subQuery) use ($club) {
                    $subQuery->whereHas('clubsPresides', function ($clubQuery) use ($club) {
                        $clubQuery->whereKey($club->id);
                    })->orWhereHas('equipesCoachees', function ($teamQuery) use ($club) {
                        $teamQuery->where('club_id', $club->id);
                    })->orWhereHas('membreEquipes', function ($memberQuery) use ($club) {
                        $memberQuery->whereHas('equipe', function ($teamQuery) use ($club) {
                            $teamQuery->where('club_id', $club->id);
                        });
                    });
                });
            });

        if (! empty($filtres['q'])) {
            $terme = $filtres['q'];
            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('type_document', 'like', "%{$terme}%")
                    ->orWhereHas('utilisateur', function ($userQuery) use ($terme) {
                        $userQuery->where('nom', 'like', "%{$terme}%")
                            ->orWhere('prenom', 'like', "%{$terme}%")
                            ->orWhere('email', 'like', "%{$terme}%");
                    });
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

    public function creer(array $donnees): Document
    {
        return Document::create($donnees)->load('utilisateur');
    }

    public function mettreAJour(Document $document, array $donnees): Document
    {
        $document->update($donnees);

        return $document->fresh('utilisateur');
    }

    public function supprimer(Document $document): void
    {
        $document->delete();
    }
}
