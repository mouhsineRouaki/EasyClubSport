<?php

namespace App\Services\President\Document;

use App\Models\Club;
use App\Models\Document;
use App\Models\User;
use App\Repositories\President\Document\DocumentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    public function __construct(
        protected DocumentRepository $documentRepository
    ) {
    }

    public function lister(User $utilisateur)
    {
        return $this->documentRepository->listerParPresident($utilisateur);
    }

    public function listerParClub(Club $club)
    {
        return $this->documentRepository->listerParClub($club);
    }

    public function creer(Club $club, array $donnees, UploadedFile $fichier): Document
    {
        $utilisateur = User::findOrFail($donnees['utilisateur_id']);

        if (! $this->utilisateurAppartientAuClub($utilisateur, $club)) {
            throw ValidationException::withMessages([
                'utilisateur_id' => 'Cet utilisateur n appartient pas a ce club.',
            ]);
        }

        $donnees['fichier'] = $fichier->store('documents', 'public');
        $donnees['date_ajout'] = now();

        return $this->documentRepository->creer($donnees);
    }

    public function mettreAJour(Document $document, array $donnees, ?UploadedFile $fichier = null): Document
    {
        if ($fichier) {
            if ($document->fichier) {
                Storage::disk('public')->delete($document->fichier);
            }

            $donnees['fichier'] = $fichier->store('documents', 'public');
            $donnees['date_ajout'] = now();
        }

        return $this->documentRepository->mettreAJour($document, $donnees);
    }

    public function supprimer(Document $document): void
    {
        if ($document->fichier) {
            Storage::disk('public')->delete($document->fichier);
        }

        $this->documentRepository->supprimer($document);
    }

    protected function utilisateurAppartientAuClub(User $utilisateur, Club $club): bool
    {
        if ((int) $club->president_id === (int) $utilisateur->id) {
            return true;
        }

        if ($utilisateur->equipesCoachees()->where('club_id', $club->id)->exists()) {
            return true;
        }

        return $utilisateur->membreEquipes()
            ->whereHas('equipe', function ($query) use ($club) {
                $query->where('club_id', $club->id);
            })
            ->exists();
    }
}
