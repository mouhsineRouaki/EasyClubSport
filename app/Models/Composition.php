<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Composition extends Model
{
    use HasFactory;

    protected $fillable = [
        'feuille_match_id',
        'utilisateur_id',
        'type_placement',
        'position_joueur',
    ];

    public function feuilleMatch(): BelongsTo
    {
        return $this->belongsTo(FeuilleMatch::class, 'feuille_match_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}