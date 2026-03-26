<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {
    }

    public function inscrire(array $donnees): array
    {
        $donnees['name'] = trim($donnees['prenom'].' '.$donnees['nom']);

        $utilisateur = $this->authRepository->creerUtilisateur($donnees);
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        return [
            'utilisateur' => $utilisateur,
            'token' => $token,
        ];
    }

    public function connecter(array $donnees): array
    {
        $utilisateur = $this->authRepository->trouverParEmail($donnees['email']);

        if (! $utilisateur || ! Hash::check($donnees['password'], $utilisateur->password)) {
            throw new AuthenticationException('Les informations de connexion sont incorrectes.');
        }

        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        return [
            'utilisateur' => $utilisateur,
            'token' => $token,
        ];
    }

    public function deconnecter(User $utilisateur): void
    {
        $utilisateur->currentAccessToken()?->delete();
    }
}
