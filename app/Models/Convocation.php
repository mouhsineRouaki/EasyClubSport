<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evenement_id',
        'utilisateur_id',
        'statut',
        'date_convocation',
        'date_confirmation',
    ];

    protected function casts(): array
    {
        return [
            'date_convocation' => 'datetime',
            'date_confirmation' => 'datetime',
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