# UniPod To-Do List

Application web de gestion de tâches, de projets et de réunions.

Ce projet sert à organiser le travail d’une équipe dans un même espace partagé. On peut y créer des espaces de travail, des tableaux de suivi, des groupes de tâches, des tâches détaillées, des réunions et des notifications.

L’objectif est simple: permettre à plusieurs personnes de voir le même projet, de comprendre quoi faire, qui fait quoi, et où en est le travail, sans devoir jongler entre plusieurs outils.

---

## Ce que fait l’application, en langage simple

L’application fonctionne comme un bureau partagé pour une équipe.

Un utilisateur arrive dans un espace de travail. Dans cet espace, il peut:
- voir les projets en cours,
- créer ou modifier des tableaux de suivi,
- découper un projet en groupes,
- ajouter des tâches,
- affecter des personnes à une tâche,
- suivre les échéances,
- consulter les réunions,
- recevoir des notifications.

Quand quelqu’un modifie une tâche ou un tableau, les autres membres peuvent voir la mise à jour sans tout recharger, grâce à des mises à jour dynamiques.

---

## Les grandes briques du projet

### 1. L’espace de travail

L’espace de travail est le conteneur principal.

Il regroupe:
- les personnes qui participent au projet,
- les tableaux,
- les tâches,
- les réunions,
- les notifications.

Chaque utilisateur peut avoir un espace courant. C’est l’espace qu’il voit en priorité lorsqu’il se connecte.

Un espace de travail peut aussi être partagé avec d’autres personnes. Le propriétaire ou un admin peut:
- ajouter un membre par e-mail,
- lui donner un rôle,
- retirer un accès,
- générer un lien d’invitation.

Si la personne n’a pas encore de compte, elle peut ouvrir le lien d’invitation, créer son compte ou se connecter, puis rejoindre automatiquement l'espace de travail.

### 2. Les tableaux

Un tableau représente un projet ou un chantier.

Exemples:
- un challenge,
- un site web,
- une partie administrative.

Chaque tableau possède:
- un nom,
- une couleur,
- un espace de travail parent.

Un tableau peut être consulté sous plusieurs formes:
- vue tableau,
- vue Kanban,
- vue calendrier.

### 3. Les groupes

Un groupe sert à découper un tableau en sous-parties logiques.

Exemples:
- cadrage,
- développement,
- livraison.

Les groupes aident à organiser les tâches par phase, par équipe ou par thème.

### 4. Les tâches

Une tâche est l’unité de travail concrète.

Une tâche peut contenir:
- un titre,
- un statut,
- une priorité,
- une date limite,
- une description riche,
- un livrable attendu,
- des obstacles,
- un ordre d’affichage.

Une tâche peut être assignée à une ou plusieurs personnes.

### 5. Les réunions

Les réunions permettent de garder une trace des points d’équipe.

On y stocke:
- le titre,
- la date,
- les participants,
- le bilan,
- les recommandations,
- les actions à faire.

### 6. Les notifications

Les notifications informent un utilisateur qu’il s’est passé quelque chose d’important:
- une mention,
- une assignation,
- une échéance,
- un commentaire,
- un changement de statut.

---

## Qui fait quoi dans le projet

### Le navigateur de l’utilisateur

Le navigateur affiche l’application, envoie les clics, les formulaires et les actions de l’utilisateur, puis reçoit les résultats à afficher.

### Laravel

Laravel est le moteur principal du projet.

Il s’occupe de:
- recevoir les demandes de l’utilisateur,
- vérifier qu’il est connecté,
- contrôler ses droits,
- lire et écrire en base de données,
- renvoyer la bonne page ou la bonne réponse.

### Les modèles

Les modèles représentent les objets métier.

Ils servent à dire à l’application:
- ce qu’est un utilisateur,
- ce qu’est un espace de travail,
- ce qu’est un tableau,
- ce qu’est un groupe,
- ce qu’est une tâche,
- ce qu’est une réunion,
- ce qu’est une notification.

Ils décrivent aussi les liens entre ces objets.

### Les routes

Les routes sont les portes d’entrée de l’application.

Elles répondent à des adresses comme:
- `/dashboard`
- `/boards`
- `/boards/{board}`
- `/meetings`

Une route dit à Laravel quoi faire quand l’utilisateur va sur une page ou clique sur une action.

### Les composants Livewire

Livewire gère les parties interactives de l’interface.

Il permet de:
- modifier une vue sans recharger toute la page,
- ouvrir et fermer des panneaux,
- déplacer ou mettre à jour des tâches,
- rafraîchir certaines parties de l’écran en direct.

### Alpine.js

Alpine.js gère les petites interactions visuelles:
- ouvrir une modale,
- afficher un menu,
- animer un panneau,
- cacher un élément au chargement,
- faire des transitions rapides.

### Les vues Blade

Les vues Blade sont les fichiers qui construisent l’interface HTML.

Elles définissent:
- les écrans,
- les composants visuels,
- les formulaires,
- les listes,
- les modales,
- les blocs réutilisables.

### Les rôles d’accès

Le projet distingue maintenant 3 rôles dans un espace de travail.

#### Admin

L’admin est la personne qui gère l'espace de travail.

Il peut:
- renommer l'espace de travail,
- supprimer l'espace de travail,
- ajouter ou retirer des membres,
- changer les rôles,
- créer, modifier et supprimer les tableaux,
- créer et modifier le contenu de l'espace de travail.

#### Membre

Le membre participe au travail quotidien.

Il peut:
- consulter l'espace de travail,
- travailler sur les tableaux et les tâches,
- utiliser les vues de suivi,
- recevoir les notifications,
- rejoindre un espace de travail partagé.

Il ne peut pas:
- gérer les membres,
- renommer l'espace de travail,
- supprimer l'espace de travail.

#### Lecture seule

Le rôle `reader` sert à consulter sans modifier.

Il peut:
- voir l'espace de travail,
- lire les tableaux, tâches, réunions et notifications.

Il ne peut pas:
- créer,
- modifier,
- supprimer,
- gérer les accès.

### La base de données

La base de données garde les informations de façon persistante.

Elle conserve:
- les comptes utilisateurs,
- les espaces de travail,
- les tableaux,
- les groupes,
- les tâches,
- les commentaires,
- les réunions,
- les notifications.

---

## Comment tout s’enchaîne pour produire un résultat

Voici le parcours d’une action, expliqué simplement.

### Exemple: créer une tâche

1. L’utilisateur remplit un formulaire.
2. Le navigateur envoie les données.
3. Laravel reçoit la demande.
4. Laravel vérifie que les données sont valides.
5. Laravel vérifie que l’utilisateur a le droit de faire l’action.
6. Laravel choisit le bon groupe ou en crée un si nécessaire.
7. Laravel enregistre la tâche en base de données.
8. Laravel déclenche un événement pour prévenir les autres parties de l’application.
9. L’interface se met à jour.
10. L’utilisateur voit immédiatement le résultat.

### Exemple: déplacer une tâche dans Kanban

1. L’utilisateur glisse une tâche vers une autre colonne.
2. Livewire capture l’action.
3. Laravel met à jour le statut ou l’ordre de la tâche.
4. La base de données est modifiée.
5. L’interface affiche la nouvelle position.
6. Si le temps réel est actif, les autres utilisateurs voient aussi la mise à jour.

### Exemple: changer d'espace de travail

1. L’utilisateur choisit un autre espace de travail.
2. Laravel vérifie qu’il a accès à cet espace.
3. Laravel enregistre ce nouvel espace comme espace courant.
4. L’utilisateur est renvoyé vers le tableau de bord.
5. Toute l’interface affiche le contexte du nouvel espace.

---

## Structure du projet

### `app/`

Contient la logique principale de l’application.

- `Models/`: les objets métier et leurs relations.
- `Livewire/`: les composants interactifs.
- `Http/Controllers/`: les contrôleurs classiques.
- `Http/Requests/`: les règles de validation des formulaires.
- `Events/`: les événements diffusés pour mettre à jour l’interface.
- `Policies/`: les règles d’autorisation.

### `routes/`

Contient les routes de l’application.

Le fichier principal est `web.php`. Il relie une adresse web à une action.

### `resources/`

Contient tout ce qui est affiché à l’écran.

- `views/`: les pages et les composants Blade.
- `css/`: les styles.
- `js/`: les scripts JavaScript.

### `database/`

Contient la structure et les données de départ.

- `migrations/`: création et évolution des tables.
- `seeders/`: initialisation minimale pour un environnement propre.
- `factories/`: génération de fausses données pour les tests.

### `public/`

Contient le point d’entrée public de l’application et les assets compilés.

---

## Ce qui a déjà été fait

### Frontend

- Le frontend principal est terminé pour le périmètre actuel du projet.
- Les vues principales des tableaux sont opérationnelles.
- Les 3 vues de tableau sont branchées sur des vraies données:
  - tableau,
  - Kanban,
  - calendrier.
- L’ajout de tâche est unifié entre les différentes vues.
- L’ajout de groupe fonctionne.
- Le panneau latéral d’une tâche fonctionne.
- Le tableau de bord affiche un état vide adapté aux nouveaux utilisateurs.
- Les pages principales existent et sont reliées:
  - calendrier,
  - rapports,
  - membres,
  - notifications,
  - paramètres.
- Des animations et transitions ont déjà été ajoutées:
  - barre de chargement en haut de page,
  - animations de modales,
  - transitions de panneaux,
  - transitions de menus,
  - gestion de `x-cloak`.
- Trix est intégré pour:
  - la description des tâches,
  - les commentaires,
  - certains formulaires de création.
- L’interface gère déjà:
  - la création de tableaux,
  - la modification de tableaux,
  - la suppression de tableaux,
  - l’affichage selon les permissions.
- Les parcours principaux sont prêts à l’usage et l’interface est jugée complète dans sa version actuelle.
- Les écrans secondaires suivent maintenant la même logique de rôle et de visibilité.

### Backend

- Les espaces de travail peuvent être créés, modifiés, supprimés et changés.
- Le partage d'espace de travail fonctionne par e-mail et par lien d’invitation.
- L'espace de travail courant est mémorisé en base et suit le contexte de navigation.
- Les tableaux sont rattachés à l'espace de travail et passent par des contrôleurs dédiés.
- Les groupes, les tâches et leurs validations métier sont gérés côté backend.
- Les tâches supportent les déplacements, les mises à jour en masse et l’assignation.
- Les réunions sont alignées sur le modèle d’équipe:
  - rattachement à l'espace de travail,
  - validation des actions liées,
  - restrictions cohérentes côté backend et côté interface.
- Les notifications disposent de leurs routes, de leurs actions de lecture et de tests dédiés.
- La logique des rôles d'espace de travail est en place:
  - `admin`,
  - `member`,
  - `reader`.
- Des policies existent déjà pour:
  - board,
  - workspace,
  - meeting,
  - item,
  - group,
  - comment,
  - notification.
- Des `FormRequest` dédiés existent déjà pour plusieurs flux critiques:
  - boards,
  - items,
  - meetings,
  - membres d'espace de travail,
  - commentaires,
  - notifications.
- Les routes critiques ont été réalignées sur le code réel et les alias obsolètes ont été retirés.
- Les tests d’autorisations, de validation, de suppression, de notification et de broadcasting couvrent déjà les flux principaux.
- Les parcours d’invitation, d’inscription avec invite en attente et d’onboarding sont couverts par des tests de bout en bout.
- Les notifications sont aussi vérifiées en contexte multi-utilisateur pour éviter les fuites entre comptes.
- Le temps réel est branché sur des canaux privés cohérents avec Reverb/Echo, avec des événements backend en place.
- Les écrans secondaires ont reçu un premier polish visuel commun:
  - menus,
  - dropdowns,
  - états vides,
  - messages d’interface les plus visibles.

### Base de données

- Le schéma principal existe déjà.
- Les tables principales sont en place:
  - `users`
  - `workspaces`
  - `workspace_user`
  - `boards`
  - `groups`
  - `items`
  - `item_user`
  - `comments`
  - `meetings`
  - `notifications`
- Les relations essentielles sont déjà définies.
- Les seeders ne chargent plus de données de démonstration par défaut.
- Les tâches ont déjà un champ de description.
- Les réunions sont stockées avec des champs structurés en tableaux.
- Le pivot `workspace_user` garde le rôle de chaque membre dans chaque espace de travail.
- Les données de tutoriel utilisateur sont aussi persistées pour l’onboarding.
- La base actuelle contient déjà les structures nécessaires pour le partage d'espace de travail et l’onboarding.

---

## Ce qu’il reste à faire

Le projet est fonctionnel, mais il reste encore quelques points à stabiliser avant de le considérer comme totalement finalisé.

### Priorité haute

- Valider le temps réel dans un environnement proche de la production.
- Garder la couverture de tests alignée avec les derniers cas limites métier.
- Vérifier les parcours d’invitation, d’onboarding et de notifications dans un vrai usage multi-utilisateur.
- Mettre à jour le README après chaque avancée significative.

### Priorité moyenne

- Réduire les petites incohérences restantes dans la documentation et les messages d’interface.

### Priorité basse

- Surveiller les performances si la base de données grossit.
- Ajouter au besoin des contraintes métier ou des index supplémentaires.
- Préparer une éventuelle persistance plus robuste des traitements asynchrones si le volume augmente.

---

## Lecture rapide du fonctionnement

Si on résume très simplement:

1. Une personne ouvre l’application.
2. Elle entre dans un espace de travail.
3. Elle consulte un tableau.
4. Elle crée ou met à jour des groupes et des tâches.
5. L’application enregistre ces changements en base.
6. L’interface se met à jour.
7. Les autres membres voient les changements.
8. Les réunions et notifications gardent l’équipe informée.

---

## Stack technique

- Laravel 13
- Livewire 4
- Alpine.js
- Tailwind CSS
- Vite
- Trix
- Laravel Echo
- Pusher / Reverb prévu pour le temps réel

---

## Lancer le projet correctement

### Prérequis

- PHP `^8.3`
- Composer
- Node.js récent avec `npm`
- Une base de données configurée dans `.env`

### Déploiement Vercel + Supabase

Si tu déploies sur Vercel avec Supabase, utilise une vraie base Postgres et pas le SQLite local.

Variables à définir dans Vercel:

- `APP_KEY`
- `APP_URL`
- `APP_DEBUG=false`
- `DB_CONNECTION=pgsql`
- `DB_URL=...` avec l'URL fournie par Supabase
- `SESSION_DRIVER=cookie`
- `CACHE_STORE=array`
- `QUEUE_CONNECTION=sync`
- `BROADCAST_CONNECTION=log`

Exemples de `DB_URL`:

- connexion directe: `postgresql://postgres:[PASSWORD]@db.[PROJECT-REF].supabase.co:5432/postgres?sslmode=require`
- pooler transaction: `postgresql://postgres.[PROJECT-REF]:[PASSWORD]@aws-0-[REGION].pooler.supabase.com:6543/postgres?sslmode=require`

Pour Vercel, le pooler transaction est généralement le meilleur choix pour éviter les soucis de connexions temporaires.

Si tu n'utilises pas encore Reverb ou Pusher en production, garde `BROADCAST_CONNECTION=log`.
Ça évite qu'une config temps réel incomplète fasse tomber le bootstrap Laravel.

### Déploiement sur Render

Le projet inclut un fichier `render.yaml` prêt à l'emploi pour Render.

**Étapes :**

1. Pour ce dépôt sur Render (Blueprints).
2. Render détecte `render.yaml` et crée automatiquement :
   - un service Web Docker (`unipod-todo`)
   - une base PostgreSQL managée (`unipod-db`)
3. Le `APP_KEY` est généré automatiquement.
4. Les migrations sont exécutées automatiquement à chaque déploiement (`php artisan migrate --force`).

**Variables configurées automatiquement :**

| Variable | Valeur |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://unipod-todo.onrender.com` |
| `DB_CONNECTION` | `pgsql` |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `QUEUE_CONNECTION` | `sync` |
| `BROADCAST_CONNECTION` | `log` |
| `LOG_CHANNEL` | `stderr` |

**⚠️ Points d'attention :**

- **APP_URL** : change l'URL dans `render.yaml` si tu renommes le service.
- **Stockage fichiers** : le disque par défaut est `local`. Le filesystem Render étant éphémère, les fichiers stockés localement seront perdus à chaque redémarrage. Utilise S3 si tu as besoin de persistence.
- **Queue worker** : `QUEUE_CONNECTION=sync` convient pour un petit usage. Pour plus de charge, utilise `QUEUE_CONNECTION=database` avec un worker dédié.
- **Temps réel** : `BROADCAST_CONNECTION=log` désactive Echo/Revers. Configure Reverb ou Pusher si tu actives le temps réel.

### Installation propre

1. Installer les dépendances PHP.
2. Installer les dépendances front.
3. Copier `.env.example` vers `.env` si le fichier n’existe pas déjà.
4. Générer la clé d’application.
5. Exécuter les migrations.

Commande la plus simple si tu pars de zéro:

```bash
composer setup
```

Ce script fait, dans l’ordre:
- `composer install`
- création de `.env` si besoin
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install --ignore-scripts`
- `npm run build`

### Lancer en développement

Le projet a déjà un script dédié:

```bash
composer dev
```

Ce script démarre en parallèle:
- le serveur Laravel,
- le worker de queue,
- le journal live `pail`,
- Vite pour le frontend.

### Option manuelle

Si tu préfères lancer les services séparément:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

Ouvre ensuite l’application sur l’URL indiquée par `php artisan serve`.

### Vérifications à faire si quelque chose ne marche pas

- Vérifier que `.env` pointe vers la bonne base de données.
- Vérifier que `APP_KEY` est bien générée.
- Vérifier que les migrations ont été appliquées.
- Vérifier que `npm run build` passe sans erreur si tu testes en mode production.
- Vérifier que le worker de queue tourne si les notifications ou le temps réel semblent inactifs.
- Vérifier que le cache Laravel a été vidé après une modification de configuration:

```bash
php artisan config:clear
```

### Commande de test

```bash
php artisan test
```

Si tu veux repartir d’une base propre en local:

```bash
php artisan migrate:fresh --seed
```

---

## Statut global

Le projet est déjà bien avancé et le frontend principal est considéré comme terminé pour le périmètre actuel.

Il possède une base fonctionnelle solide sur les 3 couches:
- frontend,
- backend,
- base de données.

Le produit est exploitable, avec un backend consolidé, une base sans données démo par défaut, et une base de tests déjà verte.
Le reste du travail porte surtout sur:
- la validation du temps réel en conditions réalistes,
- la couverture des cas limites métier,
- le polish des écrans secondaires,
- la cohérence finale de la documentation et de l’interface.

La logique métier principale est déjà en place:
- les espaces de travail peuvent être créés, partagés, renommés et supprimés,
- les rôles `admin`, `member` et `reader` existent,
- le parcours d’invitation fonctionne aussi pour les nouveaux utilisateurs,
- les vues principales sont connectées aux données réelles,
- le tutoriel d’onboarding est en place et se relance pour un nouveau compte ou un nouvel espace de travail.

Le navigateur ne dépend plus d’un état global de tutoriel: le contexte utilisateur/espace de travail déclenche le bon affichage au bon moment.

---

## Ordre de suite recommandé

1. Fiabiliser le temps réel en conditions réelles.
2. Finaliser la couverture de tests sur les cas limites métier.
3. Nettoyer les derniers écarts de documentation et de texte UI.
