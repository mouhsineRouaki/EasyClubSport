<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'president_id',
        'nom',
        'logo',
        'adresse',
        'telephone',
        'email',
        'description',
        'ville',
        'pays',
    ];

    public function president(): BelongsTo
    {
        return $this->belongsTo(User::class, 'president_id');
    }

    public function equipes(): HasMany
    {
        return $this->hasMany(Equipe::class, 'club_id');
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class, 'club_id');
    }

    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class, 'club_id');
    }
}