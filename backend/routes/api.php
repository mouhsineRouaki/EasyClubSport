<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\President\ClubController;
use App\Http\Controllers\Api\President\DashboardPresidentController;
use App\Http\Controllers\Api\President\EvenementController;
use App\Http\Controllers\Api\President\EquipeController;
use App\Http\Controllers\Api\President\ProfilPresidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/inscription', [AuthController::class, 'inscription']);
    Route::post('/connexion', [AuthController::class, 'connexion']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/moi', [AuthController::class, 'moi']);
    Route::post('/deconnexion', [AuthController::class, 'deconnexion']);
});

Route::middleware(['auth:sanctum', 'role:president'])->prefix('president')->group(function () {
    Route::get('/dashboard', [DashboardPresidentController::class, 'index']);
    Route::get('/evenements', [EvenementController::class, 'index']);

    Route::get('/profil', [ProfilPresidentController::class, 'afficher']);
    Route::put('/profil', [ProfilPresidentController::class, 'modifier']);

    Route::get('/clubs', [ClubController::class, 'index']);
    Route::post('/clubs', [ClubController::class, 'store']);
    Route::get('/clubs/{club}', [ClubController::class, 'show']);
    Route::put('/clubs/{club}', [ClubController::class, 'update']);
    Route::delete('/clubs/{club}', [ClubController::class, 'destroy']);

    Route::get('/clubs/{club}/equipes', [EquipeController::class, 'index']);
    Route::post('/clubs/{club}/equipes', [EquipeController::class, 'store']);
    Route::get('/clubs/{club}/equipes/{equipe}', [EquipeController::class, 'show']);
    Route::put('/clubs/{club}/equipes/{equipe}', [EquipeController::class, 'update']);
    Route::delete('/clubs/{club}/equipes/{equipe}', [EquipeController::class, 'destroy']);
    Route::put('/clubs/{club}/equipes/{equipe}/coach', [EquipeController::class, 'assignerCoach']);
    Route::delete('/clubs/{club}/equipes/{equipe}/coach', [EquipeController::class, 'retirerCoach']);
    Route::get('/clubs/{club}/equipes/{equipe}/joueurs', [EquipeController::class, 'listerJoueurs']);
    Route::post('/clubs/{club}/equipes/{equipe}/joueurs', [EquipeController::class, 'ajouterJoueur']);
    Route::delete('/clubs/{club}/equipes/{equipe}/joueurs/{joueur}', [EquipeController::class, 'retirerJoueur']);

    Route::get('/clubs/{club}/equipes/{equipe}/evenements', [EvenementController::class, 'indexParEquipe']);
    Route::post('/clubs/{club}/equipes/{equipe}/evenements', [EvenementController::class, 'store']);
    Route::get('/clubs/{club}/equipes/{equipe}/evenements/{evenement}', [EvenementController::class, 'show']);
    Route::put('/clubs/{club}/equipes/{equipe}/evenements/{evenement}', [EvenementController::class, 'update']);
    Route::delete('/clubs/{club}/equipes/{equipe}/evenements/{evenement}', [EvenementController::class, 'destroy']);
});
