<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function creer(User $utilisateur, Club $club): bool
    {
        return $utilisateur->role === 'president'
            && (int) $club->president_id === (int) $utilisateur->id;
    }

    public function voir(User $utilisateur, Document $document): bool
    {
        if ($utilisateur->role !== 'president') {
            return false;
        }

        return User::whereKey($document->utilisateur_id)
            ->where(function ($query) use ($utilisateur) {
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
            ->exists();
    }

    public function modifier(User $utilisateur, Document $document): bool
    {
        return $this->voir($utilisateur, $document);
    }

    public function supprimer(User $utilisateur, Document $document): bool
    {
        return $this->voir($utilisateur, $document);
    }
}
