<?php

namespace App\Repositories\President\Document;

use App\Models\Club;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DocumentRepository
{
    public function listerParPresident(User $utilisateur): Collection
    {
        return Document::with('utilisateur')
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
            })
            ->latest()
            ->get();
    }

    public function listerParClub(Club $club): Collection
    {
        return Document::with('utilisateur')
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
            })
            ->latest()
            ->get();
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
