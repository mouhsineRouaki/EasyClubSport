<?php

namespace App\Services\Notification;

use App\Events\NotificationCreee;
use App\Models\Canal;
use App\Models\Disponibilite;
use App\Models\Convocation;
use App\Models\Evenement;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Annonce;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function listerPourUtilisateur(User $utilisateur): Collection
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with(['evenement.equipe.club', 'evenement.adversaireEquipe.club', 'canal.equipe.club', 'convocation.evenement.equipe.club', 'convocation.evenement.adversaireEquipe.club'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function marquerCommeLue(Notification $notification): Notification
    {
        $notification->update([
            'est_lue' => true,
            'date_lecture' => now(),
        ]);

        return $notification->fresh(['evenement.equipe.club', 'evenement.adversaireEquipe.club', 'canal.equipe.club', 'convocation.evenement.equipe.club']);
    }

    public function marquerToutesCommeLues(User $utilisateur): int
    {
        return Notification::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->where('est_lue', false)
            ->update([
                'est_lue' => true,
                'date_lecture' => now(),
            ]);
    }

    public function creerPourUtilisateur(User $utilisateur, array $donnees): Notification
    {
        $notification = Notification::create([
            'utilisateur_id' => $utilisateur->id,
            'evenement_id' => $donnees['evenement_id'] ?? null,
            'canal_id' => $donnees['canal_id'] ?? null,
            'convocation_id' => $donnees['convocation_id'] ?? null,
            'titre' => $donnees['titre'],
            'contenu' => $donnees['contenu'],
            'type_notification' => $donnees['type_notification'] ?? 'info',
            'action' => $donnees['action'] ?? null,
            'statut_action' => $donnees['statut_action'] ?? null,
            'module_cible' => $donnees['module_cible'] ?? null,
            'cible_id' => $donnees['cible_id'] ?? null,
            'est_lue' => false,
            'date_lecture' => null,
        ])->fresh(['evenement.equipe.club', 'evenement.adversaireEquipe.club', 'canal.equipe.club', 'convocation.evenement.equipe.club', 'convocation.evenement.adversaireEquipe.club']);

        event(new NotificationCreee($notification));

        return $notification;
    }

    public function creerPourUtilisateurs(iterable $utilisateurs, array $donnees): Collection
    {
        return collect($utilisateurs)
            ->filter()
            ->unique(fn ($utilisateur) => $utilisateur instanceof User ? $utilisateur->id : null)
            ->map(fn (User $utilisateur) => $this->creerPourUtilisateur($utilisateur, $donnees))
            ->values();
    }

    public function updateOrCreatePourUtilisateur(User $utilisateur, array $criteres, array $donnees): Notification
    {
        $notification = Notification::updateOrCreate(
            array_merge($criteres, ['utilisateur_id' => $utilisateur->id]),
            [
                'evenement_id' => $donnees['evenement_id'] ?? null,
                'canal_id' => $donnees['canal_id'] ?? null,
                'convocation_id' => $donnees['convocation_id'] ?? null,
                'titre' => $donnees['titre'],
                'contenu' => $donnees['contenu'],
                'type_notification' => $donnees['type_notification'] ?? 'info',
                'action' => $donnees['action'] ?? null,
                'statut_action' => $donnees['statut_action'] ?? null,
                'module_cible' => $donnees['module_cible'] ?? null,
                'cible_id' => $donnees['cible_id'] ?? null,
                'est_lue' => $donnees['est_lue'] ?? false,
                'date_lecture' => $donnees['date_lecture'] ?? null,
            ]
        )->fresh(['evenement.equipe.club', 'evenement.adversaireEquipe.club', 'canal.equipe.club', 'convocation.evenement.equipe.club', 'convocation.evenement.adversaireEquipe.club']);

        event(new NotificationCreee($notification));

        return $notification;
    }

    public function notifierNouveauMessage(Message $message): void
    {
        $message->loadMissing(['canal.equipe.club', 'expediteur']);

        $canal = $message->canal;
        if (! $canal) {
            return;
        }

        $expediteurNom = trim(($message->expediteur?->prenom ?? '').' '.($message->expediteur?->nom ?? ''));
        $expediteurNom = $expediteurNom ?: ($message->expediteur?->name ?? 'Un membre');
        $extrait = mb_strimwidth((string) $message->contenu, 0, 80, '...');

        $destinataires = $canal->utilisateurs()
            ->where('users.id', '!=', $message->expediteur_id)
            ->get();

        $this->creerPourUtilisateurs($destinataires, [
            'canal_id' => $canal->id,
            'titre' => 'Nouveau message',
            'contenu' => "{$expediteurNom} : {$extrait}",
            'type_notification' => 'message',
            'action' => 'ouvrir_canal',
            'module_cible' => 'messagerie',
            'cible_id' => $canal->id,
        ]);
    }

    public function notifierConvocationsCrees(Evenement $evenement, Collection $convocations): void
    {
        $evenement->loadMissing(['equipe.club']);

        $convocations
            ->each(function (Convocation $convocation) use ($evenement) {
                $convocation->loadMissing(['utilisateur']);

                if (! $convocation->utilisateur) {
                    return;
                }

                $this->updateOrCreatePourUtilisateur($convocation->utilisateur, [
                    'convocation_id' => $convocation->id,
                    'action' => 'ouvrir_convocation',
                ], [
                    'evenement_id' => $evenement->id,
                    'convocation_id' => $convocation->id,
                    'titre' => 'Nouvelle convocation',
                    'contenu' => "Vous avez ete convoque pour {$evenement->titre}.",
                    'type_notification' => 'convocation',
                    'action' => 'ouvrir_convocation',
                    'statut_action' => $convocation->statut,
                    'module_cible' => 'convocations',
                    'cible_id' => $convocation->id,
                ]);
            });
    }

    public function notifierReponseConvocation(Convocation $convocation): void
    {
        $convocation->loadMissing(['utilisateur', 'evenement.equipe.club', 'evenement.equipe.coach']);

        $destinataires = collect([
            $convocation->evenement?->equipe?->coach,
            $convocation->evenement?->equipe?->club?->president,
        ])->filter();

        $joueurNom = trim(($convocation->utilisateur?->prenom ?? '').' '.($convocation->utilisateur?->nom ?? ''));
        $joueurNom = $joueurNom ?: ($convocation->utilisateur?->name ?? 'Le joueur');

        $destinataires->each(function (User $destinataire) use ($convocation, $joueurNom) {
            $this->updateOrCreatePourUtilisateur($destinataire, [
                'convocation_id' => $convocation->id,
                'action' => 'convocation_response',
            ], [
                'evenement_id' => $convocation->evenement_id,
                'convocation_id' => $convocation->id,
                'titre' => 'Reponse a une convocation',
                'contenu' => "{$joueurNom} a repondu {$convocation->statut} a la convocation.",
                'type_notification' => 'convocation',
                'action' => 'convocation_response',
                'statut_action' => $convocation->statut,
                'module_cible' => 'convocations',
                'cible_id' => $convocation->id,
            ]);
        });
    }

    public function notifierNouvelleAnnonce(Annonce $annonce): void
    {
        $annonce->loadMissing(['club.equipes.utilisateurs', 'club.president', 'auteur']);

        $club = $annonce->club;
        if (! $club) {
            return;
        }

        $destinataires = $club->equipes
            ->flatMap(fn ($equipe) => $equipe->utilisateurs)
            ->push($club->president)
            ->filter()
            ->reject(fn (User $utilisateur) => (int) $utilisateur->id === (int) $annonce->auteur_id)
            ->unique('id')
            ->values();

        $this->creerPourUtilisateurs($destinataires, [
            'titre' => 'Nouvelle annonce',
            'contenu' => "Une nouvelle annonce a ete publiee : {$annonce->titre}.",
            'type_notification' => 'info',
            'action' => 'ouvrir_annonce',
            'module_cible' => 'annonces',
            'cible_id' => $annonce->id,
        ]);
    }

    public function notifierDisponibiliteMiseAJour(Disponibilite $disponibilite): void
    {
        $disponibilite->loadMissing(['utilisateur', 'evenement.equipe.club', 'evenement.equipe.coach']);

        $evenement = $disponibilite->evenement;
        if (! $evenement) {
            return;
        }

        $joueurNom = trim(($disponibilite->utilisateur?->prenom ?? '').' '.($disponibilite->utilisateur?->nom ?? ''));
        $joueurNom = $joueurNom ?: ($disponibilite->utilisateur?->name ?? 'Le joueur');

        $destinataires = collect([
            $evenement->equipe?->coach,
            $evenement->equipe?->club?->president,
        ])->filter()->unique('id')->values();

        $this->creerPourUtilisateurs($destinataires, [
            'evenement_id' => $evenement->id,
            'titre' => 'Disponibilite mise a jour',
            'contenu' => "{$joueurNom} a indique sa disponibilite : {$disponibilite->statut}.",
            'type_notification' => 'info',
            'action' => 'ouvrir_evenement',
            'statut_action' => $disponibilite->statut,
            'module_cible' => 'evenements',
            'cible_id' => $evenement->id,
        ]);
    }

    public function notifierFeuilleMatchPubliee(Evenement $evenement): void
    {
        $evenement->loadMissing(['equipe.club.president', 'equipe.utilisateurs']);

        $destinataires = $evenement->equipe?->utilisateurs
            ?->push($evenement->equipe?->club?->president)
            ->filter()
            ->unique('id')
            ->values() ?? collect();

        $this->creerPourUtilisateurs($destinataires, [
            'evenement_id' => $evenement->id,
            'titre' => 'Feuille de match mise a jour',
            'contenu' => "La feuille de match de {$evenement->titre} a ete enregistree.",
            'type_notification' => 'info',
            'action' => 'ouvrir_evenement',
            'module_cible' => 'evenements',
            'cible_id' => $evenement->id,
        ]);
    }

    public function notifierStatistiquesMatchPubliees(Evenement $evenement): void
    {
        $evenement->loadMissing(['equipe.club.president', 'equipe.utilisateurs']);

        $destinataires = $evenement->equipe?->utilisateurs
            ?->push($evenement->equipe?->club?->president)
            ->filter()
            ->unique('id')
            ->values() ?? collect();

        $this->creerPourUtilisateurs($destinataires, [
            'evenement_id' => $evenement->id,
            'titre' => 'Statistiques du match disponibles',
            'contenu' => "Les statistiques de {$evenement->titre} ont ete publiees.",
            'type_notification' => 'info',
            'action' => 'ouvrir_evenement',
            'module_cible' => 'evenements',
            'cible_id' => $evenement->id,
        ]);
    }
}
