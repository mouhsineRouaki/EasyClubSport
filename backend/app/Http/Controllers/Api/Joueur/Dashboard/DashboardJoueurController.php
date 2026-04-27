<?php

namespace App\Http\Controllers\Api\Joueur\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Joueur\DashboardJoueurResource;
use App\Services\Joueur\Dashboard\DashboardJoueurService;

class DashboardJoueurController extends Controller
{
    public function __construct(
        protected DashboardJoueurService $dashboardJoueurService
    ) {
    }

    public function index(): DashboardJoueurResource
    {
        return new DashboardJoueurResource(
            $this->dashboardJoueurService->recupererDashboard(request()->user())
        );
    }
}
