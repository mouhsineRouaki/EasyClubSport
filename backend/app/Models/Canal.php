<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Canal extends Model
{
    use HasFactory;

    protected $table = 'canaux';

    protected $fillable = [
        'equipe_id',
        'nom',
        'type_canal',
        'description',
    ];

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function canalUtilisateurs(): HasMany
    {
        return $this->hasMany(CanalUtilisateur::class, 'canal_id');
    }

    public function utilisateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'canal_utilisateurs', 'canal_id', 'utilisateur_id')
            ->withTimestamps();
    }
}