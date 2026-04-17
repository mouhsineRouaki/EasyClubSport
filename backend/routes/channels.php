<?php

use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('equipe.{equipeId}.messages', function (User $utilisateur, int $equipeId) {
    $equipe = Equipe::query()->with('club')->find($equipeId);

    if (! $equipe) {
        return false;
    }

    if ($utilisateur->role === 'president') {
        return (int) $equipe->club?->president_id === (int) $utilisateur->id;
    }

    if ($utilisateur->role === 'coach') {
        return (int) $equipe->coach_id === (int) $utilisateur->id;
    }

    if ($utilisateur->role === 'joueur') {
        return MembreEquipe::query()
            ->where('equipe_id', $equipeId)
            ->where('utilisateur_id', $utilisateur->id)
            ->exists();
    }

    return false;
});
