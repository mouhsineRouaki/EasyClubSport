<?php

namespace App\Repositories\Joueur\Document;

use App\Models\Document;
use App\Models\User;

class DocumentJoueurRepository
{
    public function listerDocuments(User $utilisateur)
    {
        return Document::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with('utilisateur')
            ->orderByDesc('date_ajout')
            ->orderByDesc('id')
            ->get();
    }

    public function listerDocumentsRecents(User $utilisateur, int $limite = 3)
    {
        return Document::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with('utilisateur')
            ->orderByDesc('date_ajout')
            ->limit($limite)
            ->get();
    }
}
