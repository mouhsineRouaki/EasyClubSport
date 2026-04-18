<?php

namespace App\Repositories\Joueur;

use App\Events\MessageEquipeEnvoye;
use App\Models\Annonce;
use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Disponibilite;
use App\Models\Document;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\MembreEquipe;
use App\Models\Message;
use App\Models\Notification;
use App\Models\StatistiqueMatch;
use App\Models\User;

class JoueurRepository
{
    public function recupererProfil(User $utilisateur): User
    {
        return $utilisateur->fresh();
    }

    public function recupererEquipeActive(User $utilisateur): ?Equipe
    {
        return MembreEquipe::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('role_equipe', 'joueur')
            ->with(['equipe.club', 'equipe.coach'])
            ->orderByDesc('date_affectation')
            ->orderByDesc('id')
            ->first()?->equipe;
    }

    public function utilisateurAPourEquipeActive(User $utilisateur): bool
    {
        return MembreEquipe::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('role_equipe', 'joueur')
            ->exists();
    }

    public function trouverEquipeParCodeInvitation(string $codeInvitation): ?Equipe
    {
        return Equipe::query()
            ->where('code_invitation', $codeInvitation)
            ->with(['club', 'coach'])
            ->withCount([
                'membreEquipes as joueurs_total' => function ($query) {
                    $query->where('role_equipe', 'joueur');
                },
                'evenements as evenements_total',
            ])
            ->first();
    }

    public function rattacherJoueurAEquipe(User $utilisateur, Equipe $equipe): void
    {
        MembreEquipe::query()->create([
            'equipe_id' => $equipe->id,
            'utilisateur_id' => $utilisateur->id,
            'role_equipe' => 'joueur',
            'date_affectation' => now()->toDateString(),
        ]);

        $equipe->canaux()
            ->get()
            ->each(fn (Canal $canal) => $canal->utilisateurs()->syncWithoutDetaching([$utilisateur->id]));
    }

    public function listerEvenements(User $utilisateur)
    {
        $equipe = $this->recupererEquipeActive($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->with([
                'equipe.club',
                'disponibilites' => fn ($query) => $query->where('utilisateur_id', $utilisateur->id),
                'convocations' => fn ($query) => $query->where('utilisateur_id', $utilisateur->id),
            ])
            ->orderBy('date_debut')
            ->get();
    }

    public function enregistrerDisponibilite(User $utilisateur, Evenement $evenement, array $donnees): Disponibilite
    {
        return Disponibilite::updateOrCreate(
            [
                'utilisateur_id' => $utilisateur->id,
                'evenement_id' => $evenement->id,
            ],
            [
                'reponse' => $donnees['reponse'],
                'commentaire' => $donnees['commentaire'] ?? null,
                'date_reponse' => now(),
            ]
        )->fresh(['evenement.equipe.club', 'utilisateur']);
    }

    public function listerConvocations(User $utilisateur)
    {
        return Convocation::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with([
                'evenement.equipe.club',
                'evenement.adversaireEquipe.club',
            ])
            ->orderByDesc('date_convocation')
            ->orderByDesc('id')
            ->get();
    }

    public function mettreAJourConvocation(Convocation $convocation, array $donnees): Convocation
    {
        $convocation->update([
            'statut' => $donnees['statut'],
            'date_confirmation' => in_array($donnees['statut'], ['confirme', 'refuse'], true) ? now() : null,
        ]);

        return $convocation->fresh([
            'evenement.equipe.club',
            'evenement.adversaireEquipe.club',
        ]);
    }

    public function listerDocuments(User $utilisateur)
    {
        return Document::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with('utilisateur')
            ->orderByDesc('date_ajout')
            ->orderByDesc('id')
            ->get();
    }

    public function listerCanaux(User $utilisateur)
    {
        return $utilisateur->canaux()
            ->with(['equipe.club', 'utilisateurs'])
            ->latest()
            ->get();
    }

    public function utilisateurAppartientAuCanal(User $utilisateur, Canal $canal): bool
    {
        return $canal->utilisateurs()
            ->where('users.id', $utilisateur->id)
            ->exists();
    }

    public function listerMessagesParCanal(Canal $canal)
    {
        return $canal->messages()
            ->with('expediteur')
            ->oldest()
            ->get();
    }

    public function creerMessage(User $utilisateur, Canal $canal, array $donnees): Message
    {
        $message = Message::create([
            'canal_id' => $canal->id,
            'equipe_id' => $canal->equipe_id,
            'expediteur_id' => $utilisateur->id,
            'contenu' => $donnees['contenu'],
            'type_message' => 'equipe',
        ])->fresh(['expediteur', 'equipe.club', 'canal']);

        event(new MessageEquipeEnvoye($message));

        return $message;
    }

    public function mettreAJourMessage(Message $message, array $donnees): Message
    {
        $message->update([
            'contenu' => $donnees['contenu'],
        ]);

        return $message->fresh(['expediteur', 'equipe.club', 'canal']);
    }

    public function supprimerMessage(Message $message): void
    {
        $message->delete();
    }

    public function listerNotifications(User $utilisateur)
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function marquerNotificationCommeLue(Notification $notification): Notification
    {
        $notification->update([
            'est_lue' => true,
            'date_lecture' => now(),
        ]);

        return $notification->fresh();
    }

    public function marquerToutesNotificationsCommeLues(User $utilisateur): int
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('est_lue', false)
            ->update([
                'est_lue' => true,
                'date_lecture' => now(),
            ]);
    }

    public function listerStatistiques(User $utilisateur)
    {
        return StatistiqueMatch::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with(['feuilleMatch.evenement.equipe.club'])
            ->orderByDesc('id')
            ->get();
    }

    public function listerAnnoncesEquipe(User $utilisateur, int $limite = 3)
    {
        $equipe = $this->recupererEquipeActive($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Annonce::query()
            ->where('club_id', $equipe->club_id)
            ->where('est_active', true)
            ->with(['club', 'auteur'])
            ->latest()
            ->limit($limite)
            ->get();
    }

    public function listerDocumentsRecents(User $utilisateur, int $limite = 3)
    {
        return Document::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with('utilisateur')
            ->orderByDesc('date_ajout')
            ->limit($limite)
            ->get();
    }

    public function listerCanauxRecents(User $utilisateur, int $limite = 3)
    {
        return $utilisateur->canaux()
            ->with(['equipe.club', 'utilisateurs'])
            ->latest()
            ->limit($limite)
            ->get();
    }

    public function compterNotificationsNonLues(User $utilisateur): int
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('est_lue', false)
            ->count();
    }

    public function compterConvocationsEnAttente(User $utilisateur): int
    {
        return Convocation::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('statut', 'en_attente')
            ->count();
    }

    public function prochainEvenement(User $utilisateur): ?Evenement
    {
        $equipe = $this->recupererEquipeActive($utilisateur);

        if (! $equipe) {
            return null;
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->where('date_debut', '>=', now())
            ->where('statut', '!=', 'annule')
            ->with('equipe.club')
            ->orderBy('date_debut')
            ->first();
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees): User
    {
        $utilisateur->update($donnees);

        return $utilisateur->fresh();
    }

    public function listerEvenementsRecentsEquipe(User $utilisateur, int $limite = 4)
    {
        $equipe = $this->recupererEquipeActive($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->with(['equipe.club'])
            ->orderBy('date_debut')
            ->limit($limite)
            ->get();
    }

    public function listerConvocationsRecentes(User $utilisateur, int $limite = 4)
    {
        return Convocation::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with([
                'evenement.equipe.club',
                'evenement.adversaireEquipe.club',
            ])
            ->orderByDesc('date_convocation')
            ->orderByDesc('id')
            ->limit($limite)
            ->get();
    }
}
