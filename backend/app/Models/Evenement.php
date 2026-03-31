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
        'description',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
        ];
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
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