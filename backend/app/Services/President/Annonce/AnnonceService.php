<?php

namespace App\Services\President\Annonce;

use App\Services\Communication\ClubCommunicationService;
use App\Models\Annonce;
use App\Models\Club;
use App\Models\User;
use App\Repositories\President\Annonce\AnnonceRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AnnonceService
{
    public function __construct(
        protected AnnonceRepository $annonceRepository,
        protected NotificationService $notificationService,
        protected ClubCommunicationService $clubCommunicationService
    ) {
    }

    public function lister(User $utilisateur, array $filtres = [])
    {
        return $this->annonceRepository->listerParPresident($utilisateur, $filtres);
    }

    public function listerParClub(Club $club, array $filtres = [])
    {
        return $this->annonceRepository->listerParClub($club, $filtres);
    }

    public function creer(User $utilisateur, Club $club, array $donnees, ?UploadedFile $image = null): Annonce
    {
        unset($donnees['image']);

        $donnees['club_id'] = $club->id;
        $donnees['auteur_id'] = $utilisateur->id;
        $donnees['est_active'] = $donnees['est_active'] ?? true;

        if ($image) {
            $donnees['image'] = $image->store('annonces', 'public');
        }

        $annonce = $this->annonceRepository->creer($donnees);
        $this->notificationService->notifierNouvelleAnnonce($annonce);
        $this->clubCommunicationService->notifierAnnonce($utilisateur, $annonce);

        return $annonce;
    }

    public function mettreAJour(Annonce $annonce, array $donnees, ?UploadedFile $image = null): Annonce
    {
        unset($donnees['image']);

        if ($image) {
            if ($annonce->image) {
                Storage::disk('public')->delete($annonce->image);
            }

            $donnees['image'] = $image->store('annonces', 'public');
        }

        $annonce = $this->annonceRepository->mettreAJour($annonce, $donnees);
        $auteur = $annonce->auteur ?: $annonce->club?->president;

        if ($auteur) {
            $this->clubCommunicationService->notifierAnnonce($auteur, $annonce, true);
        }

        return $annonce;
    }

    public function supprimer(Annonce $annonce): void
    {
        if ($annonce->image) {
            Storage::disk('public')->delete($annonce->image);
        }

        $this->annonceRepository->supprimer($annonce);
    }
}
