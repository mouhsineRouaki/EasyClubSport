<?php

namespace App\Http\Controllers\Api\President;

use App\Http\Controllers\Controller;
use App\Http\Resources\President\DashboardPresidentResource;
use App\Services\President\DashboardPresidentService;

class DashboardPresidentController extends Controller
{
    public function __construct(
        protected DashboardPresidentService $dashboardPresidentService
    ) {
    }

    public function index(): DashboardPresidentResource
    {
        $donnees = $this->dashboardPresidentService->recupererDonnees(request()->user());

        return new DashboardPresidentResource([
            'message' => 'Tableau de bord du president recupere avec succes.',
            ...$donnees,
        ]);
    }
}
