<?php

namespace App\Services\Joueur\Document;

use App\Models\User;
use App\Repositories\Joueur\Document\DocumentJoueurRepository;

class DocumentJoueurService
{
    public function __construct(
        protected DocumentJoueurRepository $documentJoueurRepository
    ) {
    }

    public function listerDocuments(User $utilisateur)
    {
        return $this->documentJoueurRepository->listerDocuments($utilisateur);
    }
}
