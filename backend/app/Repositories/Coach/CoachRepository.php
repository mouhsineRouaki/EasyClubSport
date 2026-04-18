<?php

namespace App\Repositories\Coach;

use App\Events\MessageEquipeEnvoye;
use App\Models\Canal;
use App\Models\Convocation;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\MembreEquipe;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;

class CoachRepository
{
    public function recupererEquipeActiveCoach(User $utilisateur): ?Equipe
    {
        return Equipe::query()
            ->where('coach_id', $utilisateur->id)
            ->with(['club'])
            ->withCount([
                'membreEquipes as joueurs_total' => function ($query) {
                    $query->where('role_equipe', 'joueur');
                },
                'evenements as evenements_total',
            ])
            ->latest()
            ->first();
    }

    public function recupererProfil(User $utilisateur): User
    {
        return $utilisateur->fresh();
    }

    public function listerEquipesCoach(User $utilisateur)
    {
        return Equipe::query()
            ->where('coach_id', $utilisateur->id)
            ->with(['club'])
            ->withCount(['membreEquipes as joueurs_total' => function ($query) {
                $query->where('role_equipe', 'joueur');
            }])
            ->latest()
            ->get();
    }

    public function listerJoueursEquipe(Equipe $equipe)
    {
        return MembreEquipe::query()
            ->where('equipe_id', $equipe->id)
            ->where('role_equipe', 'joueur')
            ->with('utilisateur')
            ->orderByDesc('date_affectation')
            ->get();
    }

    public function listerEvenementsEquipe(Equipe $equipe)
    {
        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->with(['equipe.club', 'adversaireEquipe.club'])
            ->orderBy('date_debut')
            ->get();
    }

    public function creerEvenement(array $donnees): Evenement
    {
        return Evenement::create($donnees)->fresh(['equipe.club', 'adversaireEquipe.club', 'createur']);
    }

    public function mettreAJourEvenement(Evenement $evenement, array $donnees): Evenement
    {
        $evenement->update($donnees);

        return $evenement->fresh(['equipe.club', 'adversaireEquipe.club', 'createur']);
    }

    public function supprimerEvenement(Evenement $evenement): void
    {
        $evenement->delete();
    }

    public function listerConvocationsEquipe(Equipe $equipe)
    {
        return Convocation::query()
            ->whereHas('evenement', function ($query) use ($equipe) {
                $query->where('equipe_id', $equipe->id);
            })
            ->with(['evenement', 'utilisateur'])
            ->orderByDesc('date_convocation')
            ->orderByDesc('id')
            ->get();
    }

    public function creerOuMettreAJourConvocation(Evenement $evenement, int $utilisateurId, string $statut): Convocation
    {
        return Convocation::updateOrCreate(
            [
                'evenement_id' => $evenement->id,
                'utilisateur_id' => $utilisateurId,
            ],
            [
                'statut' => $statut,
                'date_convocation' => now(),
                'date_confirmation' => $statut === 'confirme' ? now() : null,
            ]
        );
    }

    public function mettreAJourConvocation(Convocation $convocation, array $donnees): Convocation
    {
        $convocation->update([
            'statut' => $donnees['statut'],
            'date_confirmation' => $donnees['statut'] === 'confirme' ? now() : ($donnees['statut'] === 'refuse' ? now() : null),
        ]);

        return $convocation->fresh(['evenement.equipe.club', 'utilisateur']);
    }

    public function listerCanaux(User $utilisateur)
    {
        return Canal::query()
            ->whereHas('equipe', function ($query) use ($utilisateur) {
                $query->where('coach_id', $utilisateur->id);
            })
            ->with(['equipe.club', 'utilisateurs'])
            ->latest()
            ->get();
    }

    public function coachPeutVoirCanal(User $utilisateur, Canal $canal): bool
    {
        return (int) $canal->equipe?->coach_id === (int) $utilisateur->id;
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
            ->with(['evenement.equipe.club', 'evenement.adversaireEquipe.club'])
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

    public function prochainEvenement(User $utilisateur): ?Evenement
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return null;
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->where('date_debut', '>=', now())
            ->where('statut', '!=', 'annule')
            ->with(['equipe.club', 'adversaireEquipe.club'])
            ->orderBy('date_debut')
            ->first();
    }

    public function compterEquipes(User $utilisateur): int
    {
        return $this->recupererEquipeActiveCoach($utilisateur) ? 1 : 0;
    }

    public function compterJoueurs(User $utilisateur): int
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return 0;
        }

        return MembreEquipe::query()
            ->where('role_equipe', 'joueur')
            ->where('equipe_id', $equipe->id)
            ->count();
    }

    public function compterEvenementsAVenir(User $utilisateur): int
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return 0;
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->where('date_debut', '>=', now())
            ->where('statut', '!=', 'annule')
            ->count();
    }

    public function compterConvocationsEnAttente(User $utilisateur): int
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return 0;
        }

        return Convocation::query()
            ->where('statut', 'en_attente')
            ->whereHas('evenement', function ($query) use ($equipe) {
                $query->where('equipe_id', $equipe->id);
            })
            ->count();
    }

    public function listerEvenementsRecents(User $utilisateur, int $limite = 3)
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Evenement::query()
            ->where('equipe_id', $equipe->id)
            ->with(['equipe.club', 'adversaireEquipe.club'])
            ->orderByDesc('date_debut')
            ->limit($limite)
            ->get();
    }

    public function listerCanauxRecents(User $utilisateur, int $limite = 3)
    {
        $equipe = $this->recupererEquipeActiveCoach($utilisateur);

        if (! $equipe) {
            return collect();
        }

        return Canal::query()
            ->where('equipe_id', $equipe->id)
            ->with('equipe.club')
            ->latest()
            ->limit($limite)
            ->get();
    }

    public function mettreAJourProfil(User $utilisateur, array $donnees): User
    {
        $utilisateur->update($donnees);

        return $utilisateur->fresh();
    }
}
