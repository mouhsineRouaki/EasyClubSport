<?php

namespace App\Services\Joueur;

use App\Repositories\Joueur\JoueurRepository;

class JoueurService
{
    public function __construct(
        protected JoueurRepository $joueurRepository
    ) {
    }
}
