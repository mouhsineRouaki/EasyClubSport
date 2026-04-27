<?php

namespace App\Services\Communication;

use App\Mail\ClubNotificationMail;
use App\Models\Annonce;
use App\Models\Club;
use App\Models\Equipe;
use App\Models\Evenement;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class ClubCommunicationService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function listerCandidatsInvitationEquipe(Equipe $equipe, array $filtres = []): Collection
    {
        $query = User::query()
            ->whereIn('role', ['joueur', 'coach'])
            ->whereDoesntHave('membreEquipes', function ($subQuery) use ($equipe) {
                $subQuery->where('equipe_id', $equipe->id);
            })
            ->orderBy('nom')
            ->orderBy('prenom');

        if (! empty($filtres['q'])) {
            $terme = trim((string) $filtres['q']);

            $query->where(function ($subQuery) use ($terme) {
                $subQuery->where('nom', 'like', "%{$terme}%")
                    ->orWhere('prenom', 'like', "%{$terme}%")
                    ->orWhere('email', 'like', "%{$terme}%");
            });
        }

        return $query
            ->select([
                'id',
                'nom',
                'prenom',
                'name',
                'email',
                'telephone',
                'photo',
                'role',
                'poste_principal',
            ])
            ->limit((int) ($filtres['limit'] ?? 24))
            ->get();
    }

    public function notifierInvitationEquipe(User $expediteur, Club $club, Equipe $equipe, Collection $destinataires, ?string $message = null): void
    {
        $club->loadMissing('president');
        $equipe->loadMissing('club');

        $contenuLibre = trim((string) $message);
        $lignes = [
            "Vous avez recu un code d invitation pour rejoindre l equipe {$equipe->nom}.",
            "Code d invitation : {$equipe->code_invitation}",
            "Club : {$club->nom}",
        ];

        if ($contenuLibre !== '') {
            $lignes[] = "Message du president : {$contenuLibre}";
        }

        $this->envoyerCampagne(
            $destinataires,
            [
                'titre' => 'Invitation a rejoindre une equipe',
                'contenu' => "Le club {$club->nom} vous invite a rejoindre l equipe {$equipe->nom}. Code : {$equipe->code_invitation}.",
                'type_notification' => 'info',
                'action' => 'team_invitation',
                'module_cible' => 'equipes',
                'cible_id' => $equipe->id,
            ],
            'Invitation EasyClubSport',
            "Invitation pour l equipe {$equipe->nom}",
            $lignes
        );
    }

    public function notifierNouvelEvenement(User $expediteur, Evenement $evenement, bool $miseAJour = false): void
    {
        $evenement->loadMissing(['equipe.club']);

        $club = $evenement->equipe?->club;
        if (! $club) {
            return;
        }

        $destinataires = $this->destinatairesClub($club, $expediteur->id);
        $verbe = $miseAJour ? 'mis a jour' : 'cree';

        $this->envoyerCampagne(
            $destinataires,
            [
                'evenement_id' => $evenement->id,
                'titre' => $miseAJour ? 'Evenement mis a jour' : 'Nouvel evenement',
                'contenu' => "Un evenement a ete {$verbe} : {$evenement->titre}.",
                'type_notification' => 'info',
                'action' => 'ouvrir_evenement',
                'module_cible' => 'evenements',
                'cible_id' => $evenement->id,
            ],
            $miseAJour ? 'Evenement mis a jour' : 'Nouvel evenement',
            $miseAJour ? 'Un evenement a ete mis a jour' : 'Un nouvel evenement a ete cree',
            [
                "Club : {$club->nom}",
                "Equipe : {$evenement->equipe?->nom}",
                "Titre : {$evenement->titre}",
                'Date : '.($evenement->date_debut?->format('d/m/Y H:i') ?? 'non definie'),
            ]
        );
    }

    public function notifierAnnonce(User $expediteur, Annonce $annonce, bool $miseAJour = false): void
    {
        $annonce->loadMissing(['club']);

        $club = $annonce->club;
        if (! $club) {
            return;
        }

        $destinataires = $this->destinatairesClub($club, $expediteur->id);

        $this->envoyerCampagne(
            $destinataires,
            [
                'titre' => $miseAJour ? 'Annonce mise a jour' : 'Nouvelle annonce',
                'contenu' => ($miseAJour ? 'Une annonce a ete mise a jour : ' : 'Une nouvelle annonce a ete publiee : ').$annonce->titre.'.',
                'type_notification' => 'info',
                'action' => 'ouvrir_annonce',
                'module_cible' => 'annonces',
                'cible_id' => $annonce->id,
            ],
            $miseAJour ? 'Annonce mise a jour' : 'Nouvelle annonce',
            $miseAJour ? 'Une annonce du club a ete mise a jour' : 'Une nouvelle annonce du club est disponible',
            [
                "Club : {$club->nom}",
                "Titre : {$annonce->titre}",
                $annonce->contenu,
            ]
        );
    }

    public function notifierAffectationEquipe(User $destinataire, Equipe $equipe, string $roleEquipe): void
    {
        $equipe->loadMissing(['club']);

        $roleLisible = $roleEquipe === 'coach' ? 'coach' : 'joueur';

        $this->envoyerCampagne(
            collect([$destinataire]),
            [
                'titre' => 'Affectation a une equipe',
                'contenu' => "Vous avez ete affecte en tant que {$roleLisible} a l equipe {$equipe->nom}.",
                'type_notification' => 'info',
                'action' => 'team_assignment',
                'module_cible' => 'equipes',
                'cible_id' => $equipe->id,
            ],
            'Affectation a une equipe',
            "Vous avez rejoint l equipe {$equipe->nom}",
            [
                "Club : {$equipe->club?->nom}",
                "Role dans l equipe : {$roleLisible}",
                "Code d invitation : {$equipe->code_invitation}",
            ]
        );
    }

    protected function destinatairesClub(Club $club, ?int $utilisateurExcluId = null): Collection
    {
        $club->loadMissing(['equipes.coach', 'equipes.utilisateurs']);

        return $club->equipes
            ->flatMap(function (Equipe $equipe) {
                return collect([$equipe->coach])
                    ->merge($equipe->utilisateurs);
            })
            ->filter()
            ->reject(fn (User $utilisateur) => $utilisateurExcluId && (int) $utilisateur->id === (int) $utilisateurExcluId)
            ->unique('id')
            ->values();
    }

    protected function envoyerCampagne(
        Collection $destinataires,
        array $notificationData,
        string $mailTitle,
        string $mailHeadline,
        array $mailLines
    ): void {
        $destinataires = $destinataires
            ->filter(fn ($utilisateur) => $utilisateur instanceof User)
            ->unique('id')
            ->values();

        if ($destinataires->isEmpty()) {
            return;
        }

        $this->notificationService->creerPourUtilisateurs($destinataires, $notificationData);

        $actionUrl = env('FRONTEND_URL', config('app.url'));

        $destinataires->each(function (User $destinataire) use ($mailTitle, $mailHeadline, $mailLines, $actionUrl) {
            if (! $destinataire->email) {
                return;
            }

            try {
                Mail::to($destinataire->email)->send(
                    new ClubNotificationMail(
                        $mailTitle,
                        $mailHeadline,
                        $mailLines,
                        'Ouvrir EasyClubSport',
                        $actionUrl
                    )
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        });
    }
}
