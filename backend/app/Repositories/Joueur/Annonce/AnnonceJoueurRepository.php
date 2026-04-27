<?php

namespace App\Repositories\Joueur\Annonce;

use App\Models\Annonce;
use App\Models\User;
use App\Repositories\Joueur\Equipe\EquipeJoueurRepository;

class AnnonceJoueurRepository
{
    public function __construct(
        protected EquipeJoueurRepository $equipeJoueurRepository
    ) {
    }

    public function listerAnnonces(User $utilisateur, array $filtres = [])
    {
        $equipe = $this->equipeJoueurRepository->recupererEquipeActive($utilisateur);
        $clubId = $equipe?->club_id;

        $query = Annonce::query()
            ->with(['club', 'auteur'])
            ->where('est_active', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($clubId) {
            $query->where('club_id', $clubId);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (! empty($filtres['q'])) {
            $terme = trim((string) $filtres['q']);

            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('titre', 'like', "%{$terme}%")
                    ->orWhere('contenu', 'like', "%{$terme}%");
            });
        }

        return $query->paginate((int) ($filtres['per_page'] ?? 8));
    }
}
