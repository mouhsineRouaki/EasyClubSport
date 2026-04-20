<?php

use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\Canal;
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

Broadcast::channel('canal.{canalId}.messages', function (User $utilisateur, int $canalId) {
    $canal = Canal::query()->with('equipe.club')->find($canalId);

    if (! $canal) {
        return false;
    }

    if ($utilisateur->role === 'president') {
        return (int) $canal->equipe?->club?->president_id === (int) $utilisateur->id;
    }

    if ($utilisateur->role === 'coach') {
        return (int) $canal->equipe?->coach_id === (int) $utilisateur->id;
    }

    if ($utilisateur->role === 'joueur') {
        return $canal->utilisateurs()
            ->where('users.id', $utilisateur->id)
            ->exists();
    }

    return false;
});

Broadcast::channel('user.{userId}.notifications', function (User $utilisateur, int $userId) {
    return (int) $utilisateur->id === (int) $userId;
});
