# Guide Dossier Par Dossier

## 1. Arborescence globale

```text
EasyClubSport/
+-- backend/
+-- frontend/
+-- docs/
+-- diagrammes/
+-- docker-compose.yml
`-- DOCKER-GUIDE.md
```

## 2. Racine du projet

### `backend/`

Le backend Laravel expose l'API, applique les regles metier, gere l'authentification, les roles, la base de donnees, les notifications et le temps reel.

### `frontend/`

Le frontend Vue affiche l'interface utilisateur, gere la navigation, stocke la session locale et appelle l'API Laravel.

### `docs/`

Ce dossier contient :

- des presentations HTML/PDF du projet
- la documentation d'architecture ajoutee dans cette phase

### `diagrammes/`

Contient des diagrammes visuels de type cas d'utilisation et diagramme de classes.

### `docker-compose.yml`

Fichier central de lancement multi-services.

### `DOCKER-GUIDE.md`

Guide d'utilisation Docker fourni avec le projet.

## 3. Backend Laravel

Arborescence principale verifiee :

```text
backend/
+-- app/
+-- bootstrap/
+-- config/
+-- database/
+-- docker/
+-- docs/
+-- public/
+-- resources/
+-- routes/
+-- storage/
+-- tests/
+-- .env
+-- .env.docker
+-- Dockerfile
+-- artisan
`-- composer.json
```

### `backend/app/`

Contient le code metier applicatif.

#### `app/Http/Controllers`

Recoit les requetes HTTP.

Dans ce projet, les controllers API sont organises par role :

- `Api/AuthController.php`
- `Api/President/...`
- `Api/Coach/...`
- `Api/Joueur/...`

Role :

- recuperer la requete
- lancer la validation
- appeler le service metier
- retourner une reponse JSON

#### `app/Http/Requests`

Contient les validations des donnees entrantes.

Exemples :

- `Auth/ConnexionRequest.php`
- `Auth/InscriptionRequest.php`
- `President/Messagerie/CreerCanalRequest.php`

Role :

- verifier les champs
- eviter d'ecrire la validation dans les controllers

#### `app/Http/Resources`

Formate les reponses JSON.

Exemples :

- `AuthResource`
- `President/Messagerie/MessageResource`
- `President/Messagerie/MessageCollection`

Role :

- donner une structure stable aux reponses API
- separer le format de sortie de la logique metier

#### `app/Http/Middleware`

Contient `RoleMiddleware.php`.

Role :

- verifier qu'un utilisateur authentifie possede le bon role

#### `app/Models`

Contient les modeles Eloquent.

Entites principales reperees :

- `User`
- `Club`
- `Equipe`
- `MembreEquipe`
- `Evenement`
- `Disponibilite`
- `Convocation`
- `Canal`
- `Message`
- `Notification`
- `Document`
- `Annonce`
- `FeuilleMatch`
- `Composition`
- `StatistiqueMatch`

Role :

- representer les tables
- declarer les relations Eloquent

#### `app/Repositories`

Couche d'acces aux donnees.

Le projet est organise par role et par domaine :

- `Repositories/President/...`
- `Repositories/Coach/...`
- `Repositories/Joueur/...`
- `AuthRepository.php`

Role :

- centraliser les requetes Eloquent
- garder les services plus lisibles

#### `app/Services`

Couche metier.

Organisation :

- `Services/President/...`
- `Services/Coach/...`
- `Services/Joueur/...`
- `Services/Notification/...`
- `Services/Evenement/...`
- `AuthService.php`

Role :

- appliquer les regles metier
- coordonner plusieurs repositories
- declencher des notifications ou des events

#### `app/Events`

Contient les events broadcastes :

- `MessageEquipeEnvoye`
- `NotificationCreee`

Role :

- envoyer des evenements temps reel vers Reverb

#### `app/Policies`

Regles fines d'autorisation.

Exemples :

- `CanalPolicy`
- `MessagePolicy`
- `ClubPolicy`
- `EvenementPolicy`

Role :

- verifier si un utilisateur a le droit d'executer une action sur une ressource

#### `app/Providers`

Le `AppServiceProvider` enregistre les policies via `Gate::policy(...)`.

#### `app/Support`

Contient des presenters / helpers.

Exemples :

- `NotificationPresenter`
- `CompositionMatchPresenter`
- `FeuilleMatchPresenter`
- `StatistiqueMatchPresenter`

Role :

- preparer certaines donnees avant leur exposition

### `backend/bootstrap/`

Gere le demarrage de Laravel.

Fichier cle :

- `bootstrap/app.php`

Dans ce projet, ce fichier a aussi une logique pour charger explicitement `.env.docker` quand l'application tourne en conteneur.

### `backend/config/`

Contient la configuration de Laravel.

Fichiers importants :

- `database.php`
- `auth.php`
- `sanctum.php`
- `broadcasting.php`
- `reverb.php`
- `session.php`
- `queue.php`

### `backend/database/`

Contient :

- `migrations/` : structure de la base
- `seeders/` : donnees de depart
- `factories/` : donnees de test

### `backend/docker/`

Contient `start.sh`.

Role :

- attendre PostgreSQL
- lancer les migrations
- lancer soit le serveur web, soit la queue, soit Reverb

### `backend/public/`

Contient le point d'entree HTTP public de Laravel.

### `backend/routes/`

Contient :

- `api.php`
- `web.php`
- `channels.php`
- `console.php`

### `backend/storage/`

Contient :

- logs
- cache
- sessions
- fichiers publics/prives

### `backend/tests/`

Contient les tests PHPUnit/Laravel.

## 4. Frontend Vue

Arborescence principale verifiee :

```text
frontend/
+-- public/
+-- src/
+-- docker/
+-- .env.docker
+-- Dockerfile
+-- package.json
`-- vite.config.js
```

### `frontend/src/main.js`

Point d'entree Vue.

Role :

- creer l'application
- brancher le router
- monter `App.vue`

### `frontend/src/App.vue`

Composant racine.

Role :

- afficher `RouterView`
- afficher le conteneur global de toasts

### `frontend/src/router/`

Contient `index.js`.

Role :

- declarer les routes `/login`, `/register`, `/president`, `/coach`, `/joueur`
- proteger les routes selon le token et le role stocke localement

### `frontend/src/features/`

Contient les fonctionnalites transverses non liees a un role unique.

Exemple verifie :

- `features/auth/views/LoginPage.vue`
- `features/auth/views/RegisterPage.vue`

### `frontend/src/shared/`

Contient tout le code reutilisable.

Sous-dossiers importants :

- `components/`
- `composables/`
- `design-system/`
- `layouts/`
- `services/`
- `session/`
- `utils/`

### `frontend/src/shared/components/`

Composants UI communs :

- composants de formulaire
- notifications
- profil
- sections de match
- layouts reutilisables

### `frontend/src/shared/services/`

Services communs frontend.

Fichiers importants :

- `apiClient.js`
- `realtimeService.js`
- `toastService.js`

Role :

- faire les appels HTTP
- gerer les WebSockets
- afficher les toasts

### `frontend/src/shared/session/`

Gestion locale de la session.

Fichiers importants :

- `sessionStorage.js`
- `useAuthSession.js`

Role :

- stocker le token API et l'utilisateur dans `localStorage`
- aider a la deconnexion
- reactualiser l'utilisateur dans les vues

### `frontend/src/shared/layouts/`

Layouts et structures visuelles partagees.

### `frontend/src/shared/composables/`

Composables Vue reutilisables.

### `frontend/src/shared/design-system/`

Style commun, theme et base design.

### `frontend/src/roles/`

Organisation principale de l'interface par role :

- `roles/president/`
- `roles/coach/`
- `roles/joueur/`

Chaque role contient ses vues, composants et parfois des sous-dossiers `services`.

### `frontend/src/assets/`

Images et assets importes dans les composants Vue.

### `frontend/public/`

Fichiers statiques servis directement.

### `frontend/src/tests/`

Configuration des tests frontend.

## 5. Resume de responsabilite

| Dossier | Role principal |
|---|---|
| `backend/app/Http/Controllers` | entree HTTP API |
| `backend/app/Http/Requests` | validation |
| `backend/app/Http/Resources` | format JSON |
| `backend/app/Services` | logique metier |
| `backend/app/Repositories` | acces donnees |
| `backend/app/Models` | tables + relations |
| `backend/app/Events` | diffusion temps reel |
| `backend/routes` | declaration routes |
| `backend/database` | schema + seed |
| `frontend/src/router` | navigation SPA |
| `frontend/src/shared/services` | API + realtime |
| `frontend/src/shared/session` | session locale |
| `frontend/src/roles` | interface par role |
