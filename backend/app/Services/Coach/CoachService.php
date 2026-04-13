<?php

namespace App\Services\Coach;

use App\Repositories\Coach\CoachRepository;

class CoachService
{
    public function __construct(
        protected CoachRepository $coachRepository
    ) {
    }
}