<?php

namespace App\Repositories\Joueur\Dashboard;

use App\Models\Annonce;
use App\Models\User;

class DashboardJoueurRepository
{
    public function listerAnnoncesEquipe(?int $clubId, int $limite = 3)
    {
        if (! $clubId) {
            return collect();
        }

        return Annonce::query()
            ->where('club_id', $clubId)
            ->where('est_active', true)
            ->with(['club', 'auteur'])
            ->latest()
            ->limit($limite)
            ->get();
    }
}
