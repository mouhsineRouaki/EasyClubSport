<?php

namespace App\Http\Controllers\Api\Joueur\Annonce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Joueur\AnnonceJoueurCollection;
use App\Models\Annonce;
use App\Services\Joueur\Annonce\AnnonceJoueurService;

class AnnonceJoueurController extends Controller
{
    public function __construct(
        protected AnnonceJoueurService $annonceJoueurService
    ) {
    }

    public function index(): AnnonceJoueurCollection
    {
        $this->authorize('voirListeJoueur', Annonce::class);

        return new AnnonceJoueurCollection(
            $this->annonceJoueurService->listerAnnonces(request()->user(), request()->only(['q', 'page', 'per_page']))
        );
    }
}
