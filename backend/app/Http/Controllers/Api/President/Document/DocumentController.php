<?php

namespace App\Http\Controllers\Api\President\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\President\Document\CreerDocumentRequest;
use App\Http\Requests\President\Document\ModifierDocumentRequest;
use App\Http\Resources\President\Document\DocumentCollection;
use App\Http\Resources\President\Document\DocumentResource;
use App\Models\Club;
use App\Models\Document;
use App\Services\President\Document\DocumentService;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ) {
    }

    public function index(): DocumentCollection
    {
        $utilisateur = request()->user();
        $filtres = $this->cleanFilters($this->paginationParams());

        $this->authorize('voirListe', Document::class);

        return new DocumentCollection($this->documentService->lister($utilisateur, $filtres));
    }

    public function indexParClub(Club $club): DocumentCollection
    {
        $filtres = $this->cleanFilters($this->paginationParams());

        $this->authorize('creer', [Document::class, $club]);

        return new DocumentCollection($this->documentService->listerParClub($club, $filtres));
    }

    public function store(CreerDocumentRequest $request, Club $club): DocumentResource
    {
        $this->authorize('creer', [Document::class, $club]);

        $document = $this->documentService->creer(
            $club,
            $request->safe()->except('fichier'),
            $request->file('fichier')
        );

        return new DocumentResource([
            'message' => 'Document cree avec succes.',
            'document' => $document,
        ]);
    }

    public function show(Document $document): DocumentResource
    {
        $this->authorize('voir', $document);

        return new DocumentResource([
            'message' => 'Details du document recuperes avec succes.',
            'document' => $document->load('utilisateur'),
        ]);
    }

    public function update(ModifierDocumentRequest $request, Document $document): DocumentResource
    {
        $this->authorize('modifier', $document);

        $document = $this->documentService->mettreAJour(
            $document,
            $request->safe()->except('fichier'),
            $request->file('fichier')
        );

        return new DocumentResource([
            'message' => 'Document modifie avec succes.',
            'document' => $document,
        ]);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('supprimer', $document);

        $this->documentService->supprimer($document);

        return response()->json([
            'status' => true,
            'message' => 'Document supprime avec succes.',
            'data' => null,
        ]);
    }
}
