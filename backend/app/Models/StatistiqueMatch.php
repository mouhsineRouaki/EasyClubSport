<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatistiqueMatch extends Model
{
    use HasFactory;

    protected $table = 'statistique_matchs';

    protected $fillable = [
        'feuille_match_id',
        'utilisateur_id',
        'score_equipe',
        'score_adversaire',
        'buts',
        'passes_decisives',
        'cartons_jaunes',
        'cartons_rouges',
        'minutes_jouees',
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