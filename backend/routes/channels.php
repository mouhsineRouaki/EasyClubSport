<?php

use App\Models\Equipe;
use App\Models\Canal;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('equipe.{equipeId}.messages', function (User $utilisateur, int $equipeId) {
    $equipe = Equipe::query()->with('club')->find($equipeId);

    if (! $equipe) {
        return false;
    }

    return $utilisateur->presidesClub($equipe->club)
        || $utilisateur->coachesEquipe($equipe)
        || $utilisateur->belongsToEquipe($equipe);
});

Broadcast::channel('canal.{canalId}.messages', function (User $utilisateur, int $canalId) {
    $canal = Canal::query()->with('equipe.club')->find($canalId);

    if (! $canal) {
        return false;
    }

    return $utilisateur->presidesClub($canal->equipe?->club)
        || $utilisateur->coachesEquipe($canal->equipe)
        || $utilisateur->belongsToCanal($canal);
});

Broadcast::channel('user.{userId}.notifications', function (User $utilisateur, int $userId) {
    return (int) $utilisateur->id === (int) $userId;
});
