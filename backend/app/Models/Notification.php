<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'evenement_id',
        'titre',
        'contenu',
        'type_notification',
        'action',
        'statut_action',
        'est_lue',
        'date_lecture',
    ];

    protected function casts(): array
    {
        return [
            'est_lue' => 'boolean',
            'date_lecture' => 'datetime',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }
}
