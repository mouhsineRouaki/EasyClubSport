<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanalUtilisateur extends Model
{
    use HasFactory;

    protected $table = 'canal_utilisateurs';

    protected $fillable = [
        'canal_id',
        'utilisateur_id',
    ];

    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class, 'canal_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}