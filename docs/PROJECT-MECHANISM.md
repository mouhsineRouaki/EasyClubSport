# Mecanisme Complet Du Projet

## 1. Comment le projet est organise

EasyClubSport est organise autour de trois axes :

1. les roles metier
2. la separation frontend / backend
3. la separation technique en couches

## 2. Separation par role

Le projet manipule trois roles principaux :

- `president`
- `coach`
- `joueur`

Cette separation existe a deux niveaux.

### Cote backend

Les routes sont segmentees par prefixe :

- `/api/president/...`
- `/api/coach/...`
- `/api/joueur/...`

Et chaque groupe est protege par :

- `auth:sanctum`
- `role:<role>`

### Cote frontend

Les vues sont segmentees dans :

- `src/roles/president/`
- `src/roles/coach/`
- `src/roles/joueur/`

Le router redirige l'utilisateur vers :

- `/president`
- `/coach`
- `/joueur`

## 3. Authentification et session

### Comment fonctionne le login

Le login passe par :

- frontend : `LoginPage.vue`
- backend : `POST /api/auth/connexion`

Le backend cree un token Sanctum avec :

- `User::createToken(...)`

Le frontend stocke ensuite :

- le token dans `localStorage` sous `token_api`
- l'utilisateur dans `localStorage` sous `utilisateur_api`

### Comment les pages protegees fonctionnent

Le fichier `frontend/src/router/index.js` :

- lit le token stocke
- lit l'utilisateur stocke
- verifie si la route demande un role
- redirige si le token manque ou si le role ne correspond pas

### Comment Laravel protege les routes

Dans `backend/routes/api.php`, les routes sensibles utilisent :

- `auth:sanctum`
- `role:president`
- `role:coach`
- `role:joueur`

### Comment le backend sait qui est connecte

Quand le frontend envoie :

```http
Authorization: Bearer <token>
```

Laravel Sanctum retrouve l'utilisateur associe au token.

Ensuite, dans les controllers, on utilise souvent :

- `request()->user()`

## 4. Cycle de vie d'une action utilisateur

### Exemple simple

Prenons une action comme "charger le dashboard coach".

#### Etape 1

L'utilisateur ouvre `/coach`.

#### Etape 2

Vue Router charge `CoachDashboardPage.vue`.

#### Etape 3

La page appelle :

- `authGet('/coach/dashboard')`
- `authGet('/auth/moi')`

#### Etape 4

`apiClient.js` construit une requete HTTP et ajoute le token Bearer.

#### Etape 5

Laravel recoit la requete dans `routes/api.php`.

#### Etape 6

La route envoie la requete vers `DashboardCoachController`.

#### Etape 7

Le controller appelle `DashboardCoachService`.

#### Etape 8

Le service appelle `DashboardCoachRepository`.

#### Etape 9

Le repository interroge les modeles Eloquent, donc PostgreSQL.

#### Etape 10

Le resultat remonte :

- repository -> service -> controller

#### Etape 11

Laravel renvoie un JSON.

#### Etape 12

Le frontend met a jour les `ref` Vue et reaffiche l'ecran.

## 5. Mecanisme backend

Le backend suit tres souvent cette chaine :

```text
Route API
  -> Controller
  -> FormRequest
  -> Service
  -> Repository
  -> Model Eloquent
  -> PostgreSQL
```

### Pourquoi cette separation est utile

#### Controller

Il reste lisible et court.

#### FormRequest

Il centralise la validation.

#### Service

Il contient la logique metier.

#### Repository

Il concentre les requetes base de donnees.

#### Resource

Il standardise le JSON de sortie.

## 6. Mecanisme frontend

Le frontend suit une logique similaire :

```text
Vue Router
  -> Vue Page
  -> shared/services/apiClient.js
  -> API Laravel
  -> reponse JSON
  -> mise a jour reactive Vue
```

### Organisation frontend

#### `features/auth`

Gere la connexion et l'inscription.

#### `roles/*`

Gere les espaces fonctionnels selon le role.

#### `shared/services`

Contient :

- l'acces API
- le realtime
- les toasts

#### `shared/session`

Gere la session locale.

## 7. Realtime et chat

Le projet a un vrai mecanisme temps reel.

### Cote backend

Les messages et notifications declenchent des events :

- `MessageEquipeEnvoye`
- `NotificationCreee`

Ces events implementent `ShouldBroadcastNow`.

Ils diffusent sur des canaux prives :

- `canal.{canalId}.messages`
- `user.{userId}.notifications`

### Cote frontend

Le fichier `shared/services/realtimeService.js` :

- cree une instance `Echo`
- configure Reverb
- envoie le token dans `authEndpoint`
- s'abonne aux canaux prives

Les pages de dashboard et de messagerie appellent :

- `subscribeToCanalMessages(...)`
- `subscribeToNotifications(...)`

### Resultat

Quand un message est envoye :

1. il est ecrit en base
2. un event Laravel est declenche
3. Reverb le pousse aux clients autorises
4. le frontend recoit l'evenement et met l'UI a jour

## 8. Base de donnees et entites

La base est PostgreSQL.

Les entites principales sont :

- utilisateurs
- clubs
- equipes
- membres d'equipe
- evenements
- disponibilites
- convocations
- canaux
- messages
- notifications
- documents
- annonces

Le coeur metier tourne autour de :

- un president gere des clubs
- un club contient des equipes
- une equipe peut avoir un coach
- des joueurs rejoignent une equipe via `membre_equipes`
- une equipe cree des evenements
- les joueurs repondent aux disponibilites et convocations
- les membres communiquent dans des canaux

## 9. Separation logique metier / technique

Le projet separe bien :

### Logique metier

Dans :

- `app/Services`

### Acces donnees

Dans :

- `app/Repositories`

### Representation base

Dans :

- `app/Models`

### Interface utilisateur

Dans :

- `frontend/src`

### Infrastructure execution

Dans :

- Docker
- PostgreSQL
- Reverb

## 10. Resume pedagogique

On peut resumer le projet comme ceci :

- Vue montre les ecrans
- Vue envoie des requetes a Laravel
- Laravel verifie le token et le role
- Laravel valide les donnees
- Laravel applique la logique metier
- Laravel lit ou modifie PostgreSQL
- Laravel renvoie du JSON
- Vue affiche le resultat
- Reverb pousse les messages et notifications en temps reel
