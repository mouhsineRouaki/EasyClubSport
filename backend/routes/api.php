<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Coach\Convocation\ConvocationCoachController;
use App\Http\Controllers\Api\Coach\Dashboard\DashboardCoachController;
use App\Http\Controllers\Api\Coach\Equipe\EquipeCoachController;
use App\Http\Controllers\Api\Coach\Evenement\EvenementCoachController;
use App\Http\Controllers\Api\Coach\Messagerie\MessagerieCoachController;
use App\Http\Controllers\Api\Coach\Notification\NotificationCoachController;
use App\Http\Controllers\Api\Coach\Profil\ProfilCoachController;
use App\Http\Controllers\Api\Joueur\Convocation\ConvocationJoueurController;
use App\Http\Controllers\Api\Joueur\Dashboard\DashboardJoueurController;
use App\Http\Controllers\Api\Joueur\Document\DocumentJoueurController;
use App\Http\Controllers\Api\Joueur\Equipe\EquipeJoueurController;
use App\Http\Controllers\Api\Joueur\Evenement\EvenementJoueurController;
use App\Http\Controllers\Api\Joueur\Messagerie\MessagerieJoueurController;
use App\Http\Controllers\Api\Joueur\Notification\NotificationJoueurController;
use App\Http\Controllers\Api\Joueur\Profil\ProfilJoueurController;
use App\Http\Controllers\Api\Joueur\Statistique\StatistiqueJoueurController;
use App\Http\Controllers\Api\President\Annonce\AnnonceController;
use App\Http\Controllers\Api\President\Club\ClubController;
use App\Http\Controllers\Api\President\Dashboard\DashboardPresidentController;
use App\Http\Controllers\Api\President\Document\DocumentController;
use App\Http\Controllers\Api\President\Evenement\EvenementController;
use App\Http\Controllers\Api\President\Equipe\EquipeController;
use App\Http\Controllers\Api\President\Messagerie\MessagerieController;
use App\Http\Controllers\Api\President\Notification\NotificationPresidentController;
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
    Route::get('/equipes/adversaires', [EquipeController::class, 'adversaires']);
    Route::get('/equipes/coachs', [EquipeController::class, 'listerCoachs']);
    Route::get('/notifications', [NotificationPresidentController::class, 'index']);
    Route::put('/notifications/{notification}/lecture', [NotificationPresidentController::class, 'marquerCommeLue']);
    Route::put('/notifications/lecture/toutes', [NotificationPresidentController::class, 'marquerToutesCommeLues']);
    Route::post('/evenements/{evenement}/invitation/acceptation', [EvenementController::class, 'accepterInvitation']);
    Route::post('/evenements/{evenement}/invitation/refus', [EvenementController::class, 'refuserInvitation']);

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
    Route::get('/clubs/{club}/equipes/{equipe}/canaux/participants', [MessagerieController::class, 'participantsEquipe']);
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
    Route::get('/clubs/{club}/equipes/{equipe}/joueurs-disponibles', [EquipeController::class, 'listerJoueursDisponibles']);
    Route::post('/clubs/{club}/equipes/{equipe}/joueurs', [EquipeController::class, 'ajouterJoueur']);
    Route::delete('/clubs/{club}/equipes/{equipe}/joueurs/{joueur}', [EquipeController::class, 'retirerJoueur']);

    Route::get('/clubs/{club}/equipes/{equipe}/evenements', [EvenementController::class, 'indexParEquipe']);
    Route::post('/clubs/{club}/equipes/{equipe}/evenements', [EvenementController::class, 'store']);
    Route::get('/clubs/{club}/equipes/{equipe}/evenements/{evenement}', [EvenementController::class, 'show']);
    Route::get('/clubs/{club}/equipes/{equipe}/evenements/{evenement}/composition', [EvenementController::class, 'composition']);
    Route::get('/clubs/{club}/equipes/{equipe}/evenements/{evenement}/feuille-match', [EvenementController::class, 'feuilleMatch']);
    Route::get('/clubs/{club}/equipes/{equipe}/evenements/{evenement}/statistiques', [EvenementController::class, 'statistiquesMatch']);
    Route::put('/clubs/{club}/equipes/{equipe}/evenements/{evenement}', [EvenementController::class, 'update']);
    Route::delete('/clubs/{club}/equipes/{equipe}/evenements/{evenement}', [EvenementController::class, 'destroy']);

    Route::get('/annonces/{annonce}', [AnnonceController::class, 'show']);
    Route::put('/annonces/{annonce}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy']);
    Route::get('/canaux/{canal}', [MessagerieController::class, 'showCanal']);
    Route::get('/canaux/{canal}/participants', [MessagerieController::class, 'participantsCanal']);
    Route::delete('/canaux/{canal}/participants/{participant}', [MessagerieController::class, 'retirerParticipant']);
    Route::get('/canaux/{canal}/messages', [MessagerieController::class, 'indexMessages']);
    Route::post('/canaux/{canal}/messages', [MessagerieController::class, 'storeMessage']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::put('/messages/{message}', [MessagerieController::class, 'updateMessage']);
    Route::delete('/messages/{message}', [MessagerieController::class, 'destroyMessage']);
});

Route::middleware(['auth:sanctum', 'role:joueur'])->prefix('joueur')->group(function () {
    Route::get('/dashboard', [DashboardJoueurController::class, 'index']);
    Route::post('/rejoindre-equipe', [EquipeJoueurController::class, 'rejoindre']);
    Route::get('/profil', [ProfilJoueurController::class, 'afficher']);
    Route::put('/profil', [ProfilJoueurController::class, 'modifier']);
    Route::get('/equipe', [EquipeJoueurController::class, 'afficher']);
    Route::get('/evenements', [EvenementJoueurController::class, 'index']);
    Route::get('/evenements/{evenement}/composition', [EvenementJoueurController::class, 'composition']);
    Route::get('/evenements/{evenement}/feuille-match', [EvenementJoueurController::class, 'feuilleMatch']);
    Route::get('/evenements/{evenement}/statistiques-match', [EvenementJoueurController::class, 'statistiques']);
    Route::put('/evenements/{evenement}/disponibilite', [EvenementJoueurController::class, 'repondreDisponibilite']);
    Route::get('/convocations', [ConvocationJoueurController::class, 'index']);
    Route::put('/convocations/{convocation}', [ConvocationJoueurController::class, 'repondre']);
    Route::get('/documents', [DocumentJoueurController::class, 'index']);
    Route::get('/canaux', [MessagerieJoueurController::class, 'indexCanaux']);
    Route::get('/canaux/{canal}/messages', [MessagerieJoueurController::class, 'indexMessages']);
    Route::post('/canaux/{canal}/messages', [MessagerieJoueurController::class, 'storeMessage']);
    Route::put('/messages/{message}', [MessagerieJoueurController::class, 'updateMessage']);
    Route::delete('/messages/{message}', [MessagerieJoueurController::class, 'destroyMessage']);
    Route::get('/notifications', [NotificationJoueurController::class, 'index']);
    Route::put('/notifications/{notification}/lecture', [NotificationJoueurController::class, 'marquerCommeLue']);
    Route::put('/notifications/lecture/toutes', [NotificationJoueurController::class, 'marquerToutesCommeLues']);
    Route::get('/statistiques', [StatistiqueJoueurController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:coach'])->prefix('coach')->group(function () {
    Route::get('/dashboard', [DashboardCoachController::class, 'index']);
    Route::get('/profil', [ProfilCoachController::class, 'afficher']);
    Route::put('/profil', [ProfilCoachController::class, 'modifier']);
    Route::get('/equipes', [EquipeCoachController::class, 'index']);
    Route::get('/equipes/{equipe}/joueurs', [EquipeCoachController::class, 'joueurs']);
    Route::post('/equipes/{equipe}/joueurs', [EquipeCoachController::class, 'storeJoueur']);
    Route::get('/equipes/{equipe}/joueurs/{joueur}', [EquipeCoachController::class, 'showJoueur']);
    Route::put('/equipes/{equipe}/joueurs/{joueur}', [EquipeCoachController::class, 'updateJoueur']);
    Route::delete('/equipes/{equipe}/joueurs/{joueur}', [EquipeCoachController::class, 'destroyJoueur']);
    Route::get('/equipes/{equipe}/evenements', [EvenementCoachController::class, 'index']);
    Route::get('/equipes/{equipe}/evenements/{evenement}/disponibilites', [EvenementCoachController::class, 'disponibilites']);
    Route::get('/equipes/{equipe}/evenements/{evenement}/composition', [EvenementCoachController::class, 'composition']);
    Route::put('/equipes/{equipe}/evenements/{evenement}/composition', [EvenementCoachController::class, 'enregistrerComposition']);
    Route::get('/equipes/{equipe}/evenements/{evenement}/feuille-match', [EvenementCoachController::class, 'feuilleMatch']);
    Route::put('/equipes/{equipe}/evenements/{evenement}/feuille-match', [EvenementCoachController::class, 'enregistrerFeuilleMatch']);
    Route::get('/equipes/{equipe}/evenements/{evenement}/statistiques-match', [EvenementCoachController::class, 'statistiques']);
    Route::put('/equipes/{equipe}/evenements/{evenement}/statistiques-match', [EvenementCoachController::class, 'enregistrerStatistiques']);
    Route::post('/equipes/{equipe}/evenements', [EvenementCoachController::class, 'creer']);
    Route::put('/equipes/{equipe}/evenements/{evenement}', [EvenementCoachController::class, 'modifier']);
    Route::delete('/equipes/{equipe}/evenements/{evenement}', [EvenementCoachController::class, 'supprimer']);
    Route::post('/evenements/{evenement}/invitation/acceptation', [EvenementCoachController::class, 'accepterInvitation']);
    Route::post('/evenements/{evenement}/invitation/refus', [EvenementCoachController::class, 'refuserInvitation']);
    Route::get('/equipes/{equipe}/convocations', [ConvocationCoachController::class, 'index']);
    Route::post('/equipes/{equipe}/evenements/{evenement}/convocations', [ConvocationCoachController::class, 'creer']);
    Route::put('/convocations/{convocation}', [ConvocationCoachController::class, 'modifier']);
    Route::get('/canaux', [MessagerieCoachController::class, 'indexCanaux']);
    Route::get('/canaux/{canal}/messages', [MessagerieCoachController::class, 'indexMessages']);
    Route::post('/canaux/{canal}/messages', [MessagerieCoachController::class, 'storeMessage']);
    Route::put('/messages/{message}', [MessagerieCoachController::class, 'updateMessage']);
    Route::delete('/messages/{message}', [MessagerieCoachController::class, 'destroyMessage']);
    Route::get('/notifications', [NotificationCoachController::class, 'index']);
    Route::put('/notifications/{notification}/lecture', [NotificationCoachController::class, 'marquerCommeLue']);
    Route::put('/notifications/lecture/toutes', [NotificationCoachController::class, 'marquerToutesCommeLues']);
});
