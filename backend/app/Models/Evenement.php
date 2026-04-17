<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Evenement extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipe_id',
        'createur_id',
        'titre',
        'type',
        'date_debut',
        'date_fin',
        'lieu',
        'adversaire',
        'adversaire_equipe_id',
        'description',
        'statut',
        'statut_invitation_adversaire',
        'invitation_adversaire_repondue_par_id',
        'invitation_adversaire_repondue_at',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'invitation_adversaire_repondue_at' => 'datetime',
        ];
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function adversaireEquipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'adversaire_equipe_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function invitationAdversaireReponduePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitation_adversaire_repondue_par_id');
    }

    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class, 'evenement_id');
    }

    public function convocations(): HasMany
    {
        return $this->hasMany(Convocation::class, 'evenement_id');
    }

    public function feuilleMatch(): HasOne
    {
        return $this->hasOne(FeuilleMatch::class, 'evenement_id');
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(CommentaireEvenement::class, 'evenement_id');
    }

    public function medias(): HasMany
    {
        return $this->hasMany(Media::class, 'evenement_id');
    }
}
