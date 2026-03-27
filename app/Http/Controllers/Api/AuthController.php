<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConnexionRequest;
use App\Http\Requests\Auth\InscriptionRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function inscription(InscriptionRequest $request): AuthResource
    {
        $resultat = $this->authService->inscrire($request->validated());

        return new AuthResource([
            'message' => 'Inscription reussie.',
            'utilisateur' => $resultat['utilisateur'],
            'token' => $resultat['token'],
        ]);
    }

    public function connexion(ConnexionRequest $request): AuthResource|JsonResponse
    {
        try {
            $resultat = $this->authService->connecter($request->validated());

            return new AuthResource([
                'message' => 'Connexion reussie.',
                'utilisateur' => $resultat['utilisateur'],
                'token' => $resultat['token'],
            ]);
        } catch (AuthenticationException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'data' => null,
            ], 401);
        }
    }

    public function deconnexion(): JsonResponse
    {
        $this->authService->deconnecter(request()->user());

        return response()->json([
            'status' => true,
            'message' => 'Deconnexion reussie.',
            'data' => null,
        ]);
    }

    public function moi(): JsonResponse
    {
        $utilisateur = request()->user();

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur connecte recupere avec succes.',
            'data' => [
                'utilisateur' => [
                    'id' => $utilisateur->id,
                    'name' => $utilisateur->name,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'email' => $utilisateur->email,
                    'telephone' => $utilisateur->telephone,
                    'adresse' => $utilisateur->adresse,
                    'photo' => $utilisateur->photo,
                    'role' => $utilisateur->role,
                    'statut' => $utilisateur->statut,
                ],
            ],
        ]);
    }
}
