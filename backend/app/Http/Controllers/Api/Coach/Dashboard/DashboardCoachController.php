<?php

namespace App\Http\Controllers\Api\Coach\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Coach\DashboardCoachResource;
use App\Services\Coach\Dashboard\DashboardCoachService;

class DashboardCoachController extends Controller
{
    public function __construct(
        protected DashboardCoachService $dashboardCoachService
    ) {
    }

    public function index(): DashboardCoachResource
    {
        return new DashboardCoachResource(
            $this->dashboardCoachService->recupererDashboard(request()->user())
        );
    }
}
