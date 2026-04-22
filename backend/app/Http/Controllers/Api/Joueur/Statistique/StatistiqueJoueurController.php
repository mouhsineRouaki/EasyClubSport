<?php

namespace App\Http\Controllers\Api\Joueur\Statistique;

use App\Http\Controllers\Controller;
use App\Http\Resources\Joueur\StatistiqueJoueurCollection;
use App\Services\Joueur\Statistique\StatistiqueJoueurService;

class StatistiqueJoueurController extends Controller
{
    public function __construct(
        protected StatistiqueJoueurService $statistiqueJoueurService
    ) {
    }

    public function index(): StatistiqueJoueurCollection
    {
        return new StatistiqueJoueurCollection(
            $this->statistiqueJoueurService->listerStatistiques(request()->user())
        );
    }
}
