# Docker Guide EasyClubSport

## 1. C'est quoi Docker

Docker est un outil qui permet de lancer un projet dans des environnements isoles et reproductibles.

En pratique, cela veut dire :
- tout le monde lance le meme projet
- avec les memes versions
- sans devoir tout installer a la main sur son ordinateur

## 2. C'est quoi une image Docker

Une image Docker est une sorte de modele.

Elle contient ce qu'il faut pour lancer une application, par exemple :
- PHP
- Node.js
- PostgreSQL

## 3. C'est quoi un container

Un container est une instance lancee a partir d'une image.

Exemple :
- l'image = le modele
- le container = l'application qui tourne vraiment

## 4. C'est quoi Docker Compose

Docker Compose sert a lancer plusieurs containers ensemble avec une seule commande.

Dans ce projet, il lance :
- le backend Laravel
- le frontend Vue
- la base PostgreSQL
- le worker de queue Laravel
- le serveur Reverb

## 5. Pourquoi on utilise un Dockerfile

Le `Dockerfile` explique comment construire l'image d'un service.

Exemple :
- quelle image de base utiliser
- quels outils installer
- quel port exposer

## 6. Pourquoi on utilise docker-compose.yml

Le `docker-compose.yml` sert a relier tous les services du projet.

C'est lui qui dit :
- quel service lancer
- quels ports ouvrir
- quels dossiers partager
- quelles variables d'environnement utiliser

## 7. Role de chaque service

- `db` : la base de donnees PostgreSQL
- `backend` : l'API Laravel
- `queue` : le worker Laravel pour les jobs en arriere-plan
- `reverb` : le serveur temps reel Laravel
- `frontend` : l'application Vue.js

## 8. Role de chaque fichier cree

- `docker-compose.yml` : lance tout le projet
- `backend/Dockerfile` : image Docker du backend Laravel
- `frontend/Dockerfile` : image Docker du frontend Vue
- `backend/.dockerignore` : evite de copier des fichiers inutiles dans l'image backend
- `frontend/.dockerignore` : evite de copier des fichiers inutiles dans l'image frontend
- `backend/.env.docker` : variables Docker du backend
- `frontend/.env.docker` : variables Docker du frontend
- `backend/docker/start.sh` : demarrage simple du backend, migration, queue ou reverb
- `frontend/docker/start.sh` : demarrage simple du frontend

## 9. Commandes principales

### Construire et lancer le projet

```bash
docker compose up --build
```

### Lancer en arriere-plan

```bash
docker compose up --build -d
```

### Arreter le projet

```bash
docker compose down
```

### Arreter et supprimer aussi les volumes

```bash
docker compose down -v
```

## 10. Comment demarrer le projet

### Etape 1

Place-toi a la racine du projet :

```bash
cd EasyClubSport
```

### Etape 2

Lance Docker Compose :

```bash
docker compose up --build
```

### Etape 3

Ouvre :

- Frontend : `http://localhost:5173`
- Backend Laravel : `http://localhost:8000`
- API Laravel : `http://localhost:8000/api`
- Reverb : `http://localhost:8081`

## 11. Comment arreter le projet

```bash
docker compose down
```

## 12. Comment rebuild si tu changes le code

Si tu modifies seulement le code, souvent un simple :

```bash
docker compose up
```

suffit, car les dossiers sont montes en volume.

Si tu modifies un Dockerfile ou une dependance importante, refais :

```bash
docker compose up --build
```

## 13. Comment voir les logs

Tous les logs :

```bash
docker compose logs
```

Logs d'un seul service :

```bash
docker compose logs backend
docker compose logs frontend
docker compose logs db
docker compose logs queue
docker compose logs reverb
```

Logs en direct :

```bash
docker compose logs -f backend
```

## 14. Comment entrer dans un container

Entrer dans le backend :

```bash
docker compose exec backend sh
```

Entrer dans le frontend :

```bash
docker compose exec frontend sh
```

Entrer dans la base :

```bash
docker compose exec db sh
```

## 15. Comment lancer les commandes Laravel

Exemples :

```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
docker compose exec backend php artisan route:list
docker compose exec backend php artisan test
```

## 16. Comment lancer les commandes npm

Exemples :

```bash
docker compose exec frontend npm install axios
docker compose exec frontend npm run build
docker compose exec frontend npm run test:run
```

## 17. Comment tester si tout fonctionne

### Frontend

Ouvre :

`http://localhost:5173`

### Backend

Ouvre :

`http://localhost:8000`

### API

Teste par exemple :

`http://localhost:8000/api/auth/moi`

Tu auras probablement une reponse d'authentification si tu n'es pas connecte, mais cela montre deja que l'API repond.

## 18. Comment lancer une migration Laravel

```bash
docker compose exec backend php artisan migrate
```

## 19. Comment installer une nouvelle dependance PHP

```bash
docker compose exec backend composer require nom/package
```

## 20. Comment installer une nouvelle dependance frontend

```bash
docker compose exec frontend npm install nom-package
```

## 21. Mini guide debutant

### Etape 1

Installer Docker Desktop

### Etape 2

Ouvrir un terminal a la racine du projet

### Etape 3

Lancer :

```bash
docker compose up --build
```

### Etape 4

Ouvrir le frontend :

`http://localhost:5173`

### Etape 5

Verifier que l'API Laravel repond :

`http://localhost:8000`

### Etape 6

Si besoin, lancer les migrations a la main :

```bash
docker compose exec backend php artisan migrate
```

### Etape 7

Quand tu as fini, arreter :

```bash
docker compose down
```

## 22. Resume simple

Dans ce projet :
- Docker evite d'installer PHP, PostgreSQL et Node.js manuellement
- Docker Compose lance tout avec une seule commande
- le frontend tourne sur `5173`
- le backend tourne sur `8000`
- la base tourne sur `5432`
- Reverb tourne sur `8081`
