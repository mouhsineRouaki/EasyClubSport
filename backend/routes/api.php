<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Joueur\JoueurController;
use App\Http\Controllers\Api\President\Annonce\AnnonceController;
use App\Http\Controllers\Api\President\Club\ClubController;
use App\Http\Controllers\Api\President\Dashboard\DashboardPresidentController;
use App\Http\Controllers\Api\President\Document\DocumentController;
use App\Http\Controllers\Api\President\Evenement\EvenementController;
use App\Http\Controllers\Api\President\Equipe\EquipeController;
use App\Http\Controllers\Api\President\Messagerie\MessagerieController;
use App\Http\Controllers\Api\President\Profil\ProfilPresidentController;
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
    Route::get('/annonces', [AnnonceController::class, 'index']);
    Route::get('/canaux', [MessagerieController::class, 'indexCanaux']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/evenements', [EvenementController::class, 'index']);

    Route::get('/profil', [ProfilPresidentController::class, 'afficher']);
    Route::put('/profil', [ProfilPresidentController::class, 'modifier']);

    Route::get('/clubs', [ClubController::class, 'index']);
    Route::post('/clubs', [ClubController::class, 'store']);
    Route::get('/clubs/{club}', [ClubController::class, 'show']);
    Route::put('/clubs/{club}', [ClubController::class, 'update']);
    Route::delete('/clubs/{club}', [ClubController::class, 'destroy']);
    Route::get('/clubs/{club}/annonces', [AnnonceController::class, 'indexParClub']);
    Route::post('/clubs/{club}/annonces', [AnnonceController::class, 'store']);
    Route::get('/clubs/{club}/equipes/{equipe}/canaux', [MessagerieController::class, 'indexCanauxParEquipe']);
    Route::post('/clubs/{club}/equipes/{equipe}/canaux', [MessagerieController::class, 'storeCanal']);
    Route::get('/clubs/{club}/documents', [DocumentController::class, 'indexParClub']);
    Route::post('/clubs/{club}/documents', [DocumentController::class, 'store']);

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

    Route::get('/annonces/{annonce}', [AnnonceController::class, 'show']);
    Route::put('/annonces/{annonce}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy']);
    Route::get('/canaux/{canal}', [MessagerieController::class, 'showCanal']);
    Route::get('/canaux/{canal}/messages', [MessagerieController::class, 'indexMessages']);
    Route::post('/canaux/{canal}/messages', [MessagerieController::class, 'storeMessage']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::put('/messages/{message}', [MessagerieController::class, 'updateMessage']);
    Route::delete('/messages/{message}', [MessagerieController::class, 'destroyMessage']);
});

Route::middleware(['auth:sanctum', 'role:joueur'])->prefix('joueur')->group(function () {
    Route::get('/dashboard', [JoueurController::class, 'dashboard']);
    Route::get('/profil', [JoueurController::class, 'afficherProfil']);
    Route::put('/profil', [JoueurController::class, 'modifierProfil']);
    Route::get('/equipe', [JoueurController::class, 'equipe']);
    Route::get('/evenements', [JoueurController::class, 'evenements']);
    Route::put('/evenements/{evenement}/disponibilite', [JoueurController::class, 'repondreDisponibilite']);
    Route::get('/convocations', [JoueurController::class, 'convocations']);
    Route::put('/convocations/{convocation}', [JoueurController::class, 'repondreConvocation']);
    Route::get('/documents', [JoueurController::class, 'documents']);
    Route::get('/canaux', [JoueurController::class, 'canaux']);
    Route::get('/canaux/{canal}/messages', [JoueurController::class, 'messages']);
    Route::post('/canaux/{canal}/messages', [JoueurController::class, 'envoyerMessage']);
    Route::put('/messages/{message}', [JoueurController::class, 'modifierMessage']);
    Route::delete('/messages/{message}', [JoueurController::class, 'supprimerMessage']);
    Route::get('/notifications', [JoueurController::class, 'notifications']);
    Route::put('/notifications/{notification}/lecture', [JoueurController::class, 'marquerNotificationCommeLue']);
    Route::put('/notifications/lecture/toutes', [JoueurController::class, 'marquerToutesNotificationsCommeLues']);
    Route::get('/statistiques', [JoueurController::class, 'statistiques']);
});
