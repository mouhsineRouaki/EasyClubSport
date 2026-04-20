# Guide De Structure Du Projet EasyClubSport

## 1. Objectif

Ce document sert de base d'organisation pour le projet `EasyClubSport`.

Le but est de garder :

- un code simple
- une structure claire
- de bonnes performances
- une logique facile a maintenir

---

## 2. Modules Principaux Du Projet

Le projet est compose de ces modules :

1. Authentification
2. Utilisateurs et roles
3. Clubs
4. Equipes
5. Membres d'equipe
6. Evenements
7. Disponibilites
8. Convocations
9. Feuille de match
10. Statistiques
11. Documents
12. Messages
13. Notifications
---

## 3. Arborescence Recommandee

```text
app/
  Http/
    Controllers/
      Api/
    Requests/
    Resources/
    Middleware/
  Models/
  Repositories/
  Services/
  Policies/

database/
  migrations/
  seeders/
  factories/

routes/
  api.php
  web.php

resources/
  views/

docs/
  guide-structure-projet.md
```

---

## 4. Role De Chaque Dossier

### `app/Models`

Contient les modeles Eloquent.

Exemples :

- `Utilisateur`
- `Club`
- `Equipe`
- `Evenement`
- `Disponibilite`
- `Convocation`

### `app/Http/Controllers/Api`

Contient les controllers API.

Le controller doit seulement :

- recevoir la requete
- appeler la validation
- appeler la logique metier
- retourner la reponse

### `app/Http/Requests`

Contient les validations.

Exemples :

- `CreerClubRequest`
- `ModifierEquipeRequest`
- `CreerEvenementRequest`

### `app/Http/Resources`

Contient le format des reponses JSON.

Exemples :

- `ClubResource`
- `EquipeResource`
- `UtilisateurResource`

### `app/Repositories`

Contient l'acces aux donnees.

Le repository sert a :

- lire les donnees
- filtrer les donnees
- enregistrer les donnees
- centraliser les requetes vers la base

Exemples :

- `ClubRepository`
- `EquipeRepository`
- `EvenementRepository`
- `UtilisateurRepository`

### `app/Services`

Contient la logique metier.

Exemples :

- `ClubService`
- `EquipeService`
- `EvenementService`
- `ConvocationService`

Le service sert a :

- appliquer les regles metier
- coordonner plusieurs repositories
- garder le controller simple
- separer la logique du stockage

### `app/Policies`

Contient les regles d'autorisation.

Exemples :

- un president peut creer un club
- un coach peut creer un evenement
- un joueur peut repondre a sa disponibilite

### `database/migrations`

Contient les fichiers de creation des tables.

### `database/seeders`

Contient les donnees de base pour le developpement et les tests.

Exemples :

- roles
- utilisateur admin
- clubs de test
- equipes de test

---

## 5. Convention De Nommage

Pour garder le projet coherent :

- les noms doivent etre clairs
- les variables peuvent etre en francais
- les classes doivent etre simples
- les methodes doivent expliquer leur role

### Exemples de noms de classes

- `Utilisateur`
- `Club`
- `Equipe`
- `MembreEquipe`
- `Evenement`
- `Disponibilite`
- `Convocation`
- `FeuilleMatch`
- `StatistiqueMatch`
- `Document`

### Exemples de noms de methodes

- `creerClub`
- `modifierEquipe`
- `ajouterJoueur`
- `creerEvenement`
- `repondreDisponibilite`

### Exemples de noms de variables

- `$utilisateur`
- `$club`
- `$equipe`
- `$evenement`
- `$joueursDisponibles`

---

## 6. Bonnes Pratiques Techniques

### Controllers legers

Il ne faut pas mettre toute la logique dans le controller.

Le controller doit rester simple.

Ordre recommande dans un controller :

1. recevoir la requete
2. utiliser un `FormRequest`
3. appeler un `Service`
4. retourner un `Resource`

### Validation separee

Toujours valider les donnees avec des `FormRequest`.

Exemple de role :

- `CreerClubRequest` valide les donnees entrantes
- `ClubService` applique la logique metier
- `ClubRepository` enregistre et lit les donnees
- `ClubResource` retourne la reponse API

### Reponses standardisees

Toutes les reponses API doivent avoir une structure stable.

Exemple :

```json
{
  "status": true,
  "message": "Club cree avec succes",
  "data": {}
}
```

### Utiliser les relations Eloquent proprement

Il faut declarer clairement :

- `hasMany`
- `belongsTo`
- `belongsToMany`
- `hasOne`

### Eviter les requetes inutiles

Toujours penser a :

- `with()`
- `select()`
- `paginate()`

### Gerer la securite des le debut

Il faut mettre :

- authentification
- autorisation
- verification des roles

### Utiliser le pattern Request + Repository + Service

Pour ce projet, on adopte cette organisation :

- `Request` pour la validation
- `Repository` pour l'acces aux donnees
- `Service` pour la logique metier

Structure conseillee :

```text
Controller -> Request -> Service -> Repository -> Model
Controller -> Resource -> Reponse JSON
```

Exemple simple :

- le controller recoit la requete
- le request valide les champs
- le service decide quoi faire
- le repository lit ou enregistre dans la base
- le resource retourne le resultat

Cette approche permet :

- un code plus propre
- une logique facile a lire
- des controllers plus courts
- une meilleure maintenance
- une meilleure reutilisation du code

---

## 7. Ordre Conseille De Developpement

Pour avancer proprement, on suit cet ordre :

1. Authentification
2. Gestion des roles
3. Utilisateurs
4. Clubs
5. Equipes
6. Membres d'equipe
7. Evenements
8. Disponibilites
9. Convocations
10. Feuille de match
11. Statistiques
12. Documents
13. Messages
14. Notifications
---

## 8. Regles De Travail Pour Le Projet

Pour les prochaines modifications :

- chaque modification doit etre expliquee
- chaque nouveau fichier doit avoir un role clair
- chaque module doit etre termine proprement avant de passer au suivant
- il faut privilegier la simplicite
- il faut eviter le code complique inutile

---

## 9. Proposition De Structure Des Modeles

Les modeles principaux du projet seront :

- `Utilisateur`
- `Club`
- `Equipe`
- `MembreEquipe`
- `Evenement`
- `Disponibilite`
- `Convocation`
- `FeuilleMatch`
- `Composition`
- `StatistiqueMatch`
- `Message`
- `Notification`
- `Document`
---

## 10. Premier Module A Construire

Le premier module conseille est :

### Authentification et gestion des roles

Pourquoi :

- tous les autres modules dependent de l'utilisateur connecte
- les droits doivent etre connus des le debut
- la structure globale du projet sera plus facile a organiser

---

## 11. Plan Pratique Pour La Suite

Le plan de travail recommande est :

1. verifier la structure Laravel actuelle
2. preparer les modeles principaux
3. verifier les migrations
4. mettre l'authentification
5. mettre les roles
6. developper les clubs
7. developper les equipes
8. continuer module par module
