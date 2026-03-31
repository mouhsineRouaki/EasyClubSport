<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'coach_id',
        'nom',
        'categorie',
        'logo',
        'code_invitation',
        'statut',
        'description',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function membreEquipes(): HasMany
    {
        return $this->hasMany(MembreEquipe::class, 'equipe_id');
    }

    public function utilisateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'membre_equipes', 'equipe_id', 'utilisateur_id')
            ->withPivot('role_equipe', 'date_affectation')
            ->withTimestamps();
    }

    public function evenements(): HasMany
    {
        return $this->hasMany(Evenement::class, 'equipe_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'equipe_id');
    }

    public function canaux(): HasMany
    {
        return $this->hasMany(Canal::class, 'equipe_id');
    }
}