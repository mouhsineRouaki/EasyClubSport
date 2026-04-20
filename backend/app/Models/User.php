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
}
