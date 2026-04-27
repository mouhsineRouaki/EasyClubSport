<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'adresse',
        'photo',
        'numero_joueur',
        'poste_principal',
        'poste_secondaire',
        'pied_fort',
        'note_globale',
        'attaque',
        'defense',
        'vitesse',
        'passe',
        'dribble',
        'physique',
        'role',
        'statut',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'numero_joueur' => 'integer',
            'note_globale' => 'integer',
            'attaque' => 'integer',
            'defense' => 'integer',
            'vitesse' => 'integer',
            'passe' => 'integer',
            'dribble' => 'integer',
            'physique' => 'integer',
        ];
    }

    public function clubsPresides(): HasMany
    {
        return $this->hasMany(Club::class, 'president_id');
    }

    public function equipesCoachees(): HasMany
    {
        return $this->hasMany(Equipe::class, 'coach_id');
    }

    public function membreEquipes(): HasMany
    {
        return $this->hasMany(MembreEquipe::class, 'utilisateur_id');
    }

    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'membre_equipes', 'utilisateur_id', 'equipe_id')
            ->withPivot('role_equipe', 'date_affectation')
            ->withTimestamps();
    }

    public function evenementsCrees(): HasMany
    {
        return $this->hasMany(Evenement::class, 'createur_id');
    }

    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class, 'utilisateur_id');
    }

    public function convocations(): HasMany
    {
        return $this->hasMany(Convocation::class, 'utilisateur_id');
    }

    public function compositions(): HasMany
    {
        return $this->hasMany(Composition::class, 'utilisateur_id');
    }

    public function statistiquesMatchs(): HasMany
    {
        return $this->hasMany(StatistiqueMatch::class, 'utilisateur_id');
    }

    public function messagesEnvoyes(): HasMany
    {
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    public function notificationsSysteme(): HasMany
    {
        return $this->hasMany(Notification::class, 'utilisateur_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'utilisateur_id');
    }

    public function canaux(): BelongsToMany
    {
        return $this->belongsToMany(Canal::class, 'canal_utilisateurs', 'utilisateur_id', 'canal_id')
            ->withTimestamps();
    }

    public function commentairesEvenements(): HasMany
    {
        return $this->hasMany(CommentaireEvenement::class, 'utilisateur_id');
    }

    public function medias(): HasMany
    {
        return $this->hasMany(Media::class, 'utilisateur_id');
    }

    public function annoncesPubliees(): HasMany
    {
        return $this->hasMany(Annonce::class, 'auteur_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isPresident(): bool
    {
        return $this->hasRole('president');
    }

    public function isCoach(): bool
    {
        return $this->hasRole('coach');
    }

    public function isJoueur(): bool
    {
        return $this->hasRole('joueur');
    }

    public function presidesClub(?Club $club): bool
    {
        return $this->isPresident() && (int) $club?->president_id === (int) $this->id;
    }

    public function coachesEquipe(?Equipe $equipe): bool
    {
        return $this->isCoach() && (int) $equipe?->coach_id === (int) $this->id;
    }

    public function belongsToEquipe(?Equipe $equipe): bool
    {
        if (! $this->isJoueur() || ! $equipe) {
            return false;
        }

        return $this->equipes()
            ->where('equipes.id', $equipe->id)
            ->exists();
    }

    public function belongsToCanal(?Canal $canal): bool
    {
        if (! $canal) {
            return false;
        }

        return $this->canaux()
            ->where('canaux.id', $canal->id)
            ->exists();
    }

    public function ownsNotification(?Notification $notification): bool
    {
        return (int) $notification?->utilisateur_id === (int) $this->id;
    }

    public function ownsConvocation(?Convocation $convocation): bool
    {
        return (int) $convocation?->utilisateur_id === (int) $this->id;
    }

    public function ownsDocument(?Document $document): bool
    {
        return (int) $document?->utilisateur_id === (int) $this->id;
    }
}
