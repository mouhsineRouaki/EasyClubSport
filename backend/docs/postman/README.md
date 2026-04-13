# Documentation Postman EasyClubSport

## Emplacement
- `backend/docs/postman/easyclubsport-api.postman_collection.json`
- `backend/docs/postman/easyclubsport-local.postman_environment.json`

## Etat actuel de l API
La collection couvre actuellement :
- authentification
- profil president
- clubs president
- equipes president
- joueur

## Partie Joueur disponible
Routes documentees dans Postman :
- `GET /api/joueur/dashboard`
- `GET /api/joueur/profil`
- `PUT /api/joueur/profil`
- `GET /api/joueur/equipe`
- `GET /api/joueur/evenements`
- `PUT /api/joueur/evenements/{evenement}/disponibilite`
- `GET /api/joueur/convocations`
- `PUT /api/joueur/convocations/{convocation}`
- `GET /api/joueur/documents`
- `GET /api/joueur/canaux`
- `GET /api/joueur/canaux/{canal}/messages`
- `POST /api/joueur/canaux/{canal}/messages`
- `PUT /api/joueur/messages/{message}`
- `DELETE /api/joueur/messages/{message}`
- `GET /api/joueur/notifications`
- `PUT /api/joueur/notifications/{notification}/lecture`
- `PUT /api/joueur/notifications/lecture/toutes`
- `GET /api/joueur/statistiques`

## Variables d environnement utiles
- `base_url`
- `token_api`
- `club_id`
- `equipe_id`
- `coach_id`
- `joueur_id`
- `event_id`
- `convocation_id`
- `canal_id`
- `message_id`
- `notification_id`

## Ordre conseille de test Joueur
1. `Inscription Joueur`
2. `Connexion Joueur` si tu choisis un login joueur manuel, ou reutiliser `Connexion President` pour les routes president
3. `Afficher Profil Joueur`
4. `Afficher Equipe Joueur`
5. `Lister Evenements Joueur`
6. `Repondre Disponibilite`
7. `Lister Convocations`
8. `Repondre Convocation`
9. `Lister Canaux Joueur`
10. `Envoyer Message Joueur`
11. `Lister Notifications Joueur`
12. `Afficher Statistiques Joueur`
13. `Dashboard Joueur`
