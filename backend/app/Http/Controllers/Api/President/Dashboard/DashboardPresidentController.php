<?php

namespace App\Http\Controllers\Api\President\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\President\Dashboard\DashboardPresidentResource;
use App\Services\President\Dashboard\DashboardPresidentService;

class DashboardPresidentController extends Controller
{
    public function __construct(
        protected DashboardPresidentService $dashboardPresidentService
    ) {
    }

    public function index(): DashboardPresidentResource
    {
        $utilisateur = request()->user();

        return new DashboardPresidentResource(
            $this->dashboardPresidentService->recuperer($utilisateur)
        );
    }
}

