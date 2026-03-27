<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function trouverParEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function creerUtilisateur(array $donnees): User
    {
        return User::create($donnees);
    }

    public function creerToken(User $utilisateur, string $nomToken = 'auth_token'): string
    {
        return $utilisateur->createToken($nomToken)->plainTextToken;
    }

    public function supprimerTokenActuel(User $utilisateur): void
    {
        $utilisateur->currentAccessToken()?->delete();
    }
}
