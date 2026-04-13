# Documentation Postman EasyClubSport

## Emplacement
- `backend/docs/postman/easyclubsport-api.postman_collection.json`
- `backend/docs/postman/easyclubsport-local.postman_environment.json`

## Objectif
Ces fichiers servent a tester l'etat reel de l'API backend avec Postman. La collection suit les routes actuellement exposees dans `backend/routes/api.php`.

## Modules actuellement documentes
- Authentification
- President
- Joueur
- Coach

## Format de reponse le plus courant
La plupart des endpoints renvoient une structure proche de celle-ci :

```json
{
  "status": true,
  "message": "Operation reussie.",
  "data": {}
}
```

## Variables d'environnement utiles
- `base_url` : URL locale de l'API, par defaut `http://127.0.0.1:8000/api`
- `token_api` : token Bearer reutilise par les routes protegees
- `president_id`
- `club_id`
- `equipe_id`
- `coach_id`
- `joueur_id`
- `annonce_id`
- `document_id`
- `event_id`
- `convocation_id`
- `canal_id`
- `message_id`
- `notification_id`

## Scripts Postman deja places
Certaines requetes enregistrent automatiquement des variables dans la collection :
- `Connexion President`, `Connexion Coach`, `Connexion Joueur` enregistrent `token_api`
- `Inscription President`, `Inscription Coach`, `Inscription Joueur` enregistrent l'identifiant utilisateur si la reponse le contient
- certaines creations comme evenement, message ou convocation enregistrent aussi leur identifiant

## Etat actuel de l'API par role

### 1. Authentification
Routes documentees :
- `POST /api/auth/inscription`
- `POST /api/auth/connexion`
- `GET /api/auth/moi`
- `POST /api/auth/deconnexion`

Usage conseille :
1. inscrire les comptes de test
2. utiliser la connexion du role voulu
3. laisser le script stocker `token_api`

### 2. President
La collection couvre les blocs suivants :
- dashboard
- profil
- clubs
- equipes
- affectation du coach
- gestion des joueurs d'equipe
- evenements
- annonces
- documents
- canaux et messages

Routes principales documentees :
- `GET /api/president/dashboard`
- `GET|PUT /api/president/profil`
- `GET|POST|GET|PUT|DELETE /api/president/clubs...`
- `GET|POST|GET|PUT|DELETE /api/president/clubs/{club}/equipes...`
- `PUT|DELETE /api/president/clubs/{club}/equipes/{equipe}/coach`
- `GET|POST|DELETE /api/president/clubs/{club}/equipes/{equipe}/joueurs...`
- `GET|POST|GET|PUT|DELETE /api/president/clubs/{club}/equipes/{equipe}/evenements...`
- `GET|POST /api/president/clubs/{club}/annonces`
- `GET|PUT|DELETE /api/president/annonces/{annonce}`
- `GET|POST /api/president/clubs/{club}/documents`
- `GET|PUT|DELETE /api/president/documents/{document}`
- `GET /api/president/canaux`
- `GET /api/president/canaux/{canal}`
- `GET|POST /api/president/canaux/{canal}/messages`
- `PUT|DELETE /api/president/messages/{message}`

### 3. Joueur
La collection couvre :
- dashboard
- profil
- equipe active
- evenements
- disponibilites
- convocations
- documents
- canaux et messages
- notifications
- statistiques

Routes documentees :
- `GET /api/joueur/dashboard`
- `GET|PUT /api/joueur/profil`
- `GET /api/joueur/equipe`
- `GET /api/joueur/evenements`
- `PUT /api/joueur/evenements/{evenement}/disponibilite`
- `GET /api/joueur/convocations`
- `PUT /api/joueur/convocations/{convocation}`
- `GET /api/joueur/documents`
- `GET /api/joueur/canaux`
- `GET|POST /api/joueur/canaux/{canal}/messages`
- `PUT|DELETE /api/joueur/messages/{message}`
- `GET /api/joueur/notifications`
- `PUT /api/joueur/notifications/{notification}/lecture`
- `PUT /api/joueur/notifications/lecture/toutes`
- `GET /api/joueur/statistiques`

### 4. Coach
La collection couvre :
- dashboard
- profil
- equipes coachees
- joueurs d'une equipe
- evenements d'equipe
- convocations
- canaux et messages
- notifications

Routes documentees :
- `GET /api/coach/dashboard`
- `GET|PUT /api/coach/profil`
- `GET /api/coach/equipes`
- `GET /api/coach/equipes/{equipe}/joueurs`
- `GET|POST|PUT|DELETE /api/coach/equipes/{equipe}/evenements...`
- `GET /api/coach/equipes/{equipe}/convocations`
- `POST /api/coach/equipes/{equipe}/evenements/{evenement}/convocations`
- `PUT /api/coach/convocations/{convocation}`
- `GET /api/coach/canaux`
- `GET|POST /api/coach/canaux/{canal}/messages`
- `PUT|DELETE /api/coach/messages/{message}`
- `GET /api/coach/notifications`
- `PUT /api/coach/notifications/{notification}/lecture`
- `PUT /api/coach/notifications/lecture/toutes`

## Ordre conseille pour tester le backend
1. lancer Laravel avec `php artisan serve` dans `backend`
2. importer la collection et l'environnement
3. executer `Inscription President`, `Inscription Coach`, `Inscription Joueur`
4. executer `Connexion President` puis tester les routes President
5. executer `Connexion Joueur` puis tester les routes Joueur
6. executer `Connexion Coach` puis tester les routes Coach

## Remarques utiles
- les routes protegees attendent `Authorization: Bearer {{token_api}}`
- si une route retourne `401` ou `403`, verifier le role connecte et la presence du token
- certaines routes president ont besoin de `club_id`, `equipe_id`, `annonce_id`, `document_id`, `event_id`, `canal_id` ou `message_id`
- certaines routes joueur et coach reutilisent aussi `event_id`, `convocation_id`, `canal_id`, `message_id` et `notification_id`

## Ce que la documentation permet de comprendre
Avec cette collection, tu peux relire rapidement l'etat actuel du projet sans parcourir tous les controllers :
- quels modules sont deja exposes en API
- quels roles existent reellement
- quelles routes sont testables aujourd'hui
- quelles variables sont necessaires pour les scenarios Postman