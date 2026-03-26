<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilites';

    protected $fillable = [
        'evenement_id',
        'utilisateur_id',
        'reponse',
        'commentaire',
        'date_reponse',
    ];

    protected function casts(): array
    {
        return [
            'date_reponse' => 'datetime',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}