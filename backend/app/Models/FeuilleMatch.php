<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeuilleMatch extends Model
{
    use HasFactory;

    protected $table = 'feuille_matchs';

    protected $fillable = [
        'evenement_id',
        'formation',
        'notes',
        'est_validee',
        'score_equipe',
        'score_adversaire',
        'resume_match',
    ];

    protected function casts(): array
    {
        return [
            'est_validee' => 'boolean',
            'score_equipe' => 'integer',
            'score_adversaire' => 'integer',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function compositions(): HasMany
    {
        return $this->hasMany(Composition::class, 'feuille_match_id');
    }

    public function statistiquesMatchs(): HasMany
    {
        return $this->hasMany(StatistiqueMatch::class, 'feuille_match_id');
    }
}
