<?php

namespace App\Http\Controllers\Api\Joueur\Document;

use App\Http\Controllers\Controller;
use App\Http\Resources\Joueur\DocumentJoueurCollection;
use App\Models\Document;
use App\Services\Joueur\Document\DocumentJoueurService;

class DocumentJoueurController extends Controller
{
    public function __construct(
        protected DocumentJoueurService $documentJoueurService
    ) {
    }

    public function index(): DocumentJoueurCollection
    {
        $this->authorize('voirListe', Document::class);

        return new DocumentJoueurCollection(
            $this->documentJoueurService->listerDocuments(request()->user())
        );
    }
}
