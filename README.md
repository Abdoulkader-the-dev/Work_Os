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

Un workspace peut aussi être partagé avec d’autres personnes. Le propriétaire ou un admin peut:
- ajouter un membre par e-mail,
- lui donner un rôle,
- retirer un accès,
- générer un lien d’invitation.

Si la personne n’a pas encore de compte, elle peut ouvrir le lien d’invitation, créer son compte ou se connecter, puis rejoindre automatiquement le workspace.

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

Le projet distingue maintenant 3 rôles dans un workspace.

#### Admin

L’admin est la personne qui gère le workspace.

Il peut:
- renommer le workspace,
- supprimer le workspace,
- ajouter ou retirer des membres,
- changer les rôles,
- créer, modifier et supprimer les boards,
- créer et modifier le contenu du workspace.

#### Membre

Le membre participe au travail quotidien.

Il peut:
- consulter le workspace,
- travailler sur les boards et les tâches,
- utiliser les vues de suivi,
- recevoir les notifications,
- rejoindre un workspace partagé.

Il ne peut pas:
- gérer les membres,
- renommer le workspace,
- supprimer le workspace.

#### Lecture seule

Le rôle `reader` sert à consulter sans modifier.

Il peut:
- voir le workspace,
- lire les boards, tâches, réunions et notifications.

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

### Exemple: changer de workspace

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
- `seeders/`: données de démonstration.
- `factories/`: génération de fausses données pour les tests.

### `public/`

Contient le point d’entrée public de l’application et les assets compilés.

---

## Ce qui a déjà été fait

### Frontend

- Le frontend principal est terminé pour le périmètre actuel du projet.
- Les vues principales des tableaux sont opérationnelles.
- Les 3 vues de board sont branchées sur des vraies données:
  - tableau,
  - Kanban,
  - calendrier.
- L’ajout de tâche est unifié entre les différentes vues.
- L’ajout de groupe fonctionne.
- Le panneau latéral d’une tâche fonctionne.
- Le dashboard affiche un état vide adapté aux nouveaux utilisateurs.
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
  - la création de boards,
  - la modification de boards,
  - la suppression de boards,
  - l’affichage selon les permissions.
- Les parcours principaux sont prêts à l’usage et l’interface est jugée complète dans sa version actuelle.
- Les écrans secondaires suivent maintenant la même logique de rôle et de visibilité.

### Backend

- Les workspaces peuvent être créés et changés.
- Les workspaces peuvent être partagés par e-mail ou par lien d’invitation.
- Le workspace courant est mémorisé en base.
- Les boards peuvent être:
  - créés,
  - consultés,
  - modifiés,
  - supprimés.
- Les groupes peuvent être créés, modifiés partiellement et supprimés.
- Les tâches peuvent être:
  - créées,
  - modifiées,
  - supprimées,
  - déplacées,
  - mises à jour en masse.
- Les réunions peuvent être gérées via Livewire.
- Des règles de validation existent déjà pour les boards.
- Le temps réel est branché sur les vues board.
- Les notifications de base sont déjà en place.
- Des policies existent pour:
  - board,
  - workspace,
  - meeting.
- La logique des rôles de workspace est désormais explicite:
  - `admin`,
  - `member`,
  - `reader`.
- Des `FormRequest` dédiés ont commencé à remplacer les validations en closure pour les membres de workspace.
- Des tests d’autorisations ont commencé à couvrir les cas principaux:
  - admin autorisé,
  - member limité,
  - reader en lecture seule.

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
- Des données de démonstration existent déjà dans les seeders.
- Les tâches ont déjà un champ de description.
- Les réunions sont stockées avec des champs structurés en tableaux.
- Le pivot `workspace_user` garde le rôle de chaque membre dans chaque workspace.
- Les données de tutoriel utilisateur sont aussi persistées pour l’onboarding.
- La base actuelle contient déjà les structures nécessaires pour le partage de workspace et l’onboarding.

---

## Ce qu’il reste à faire

### Frontend

- Harmoniser encore l’interface sur certains écrans.
- Unifier les patterns visuels de certains dropdowns et notifications.
- Finir le polish UI des éléments encore incohérents.
- Renforcer les retours visuels sur certaines actions.
- Vérifier que toutes les pages gardent une expérience homogène sur desktop et mobile.
- Cacher encore plus finement les actions UI selon le rôle `reader` sur tous les écrans secondaires.

### Backend

- Extraire davantage de logique hors des closures dans `routes/web.php`.
- Créer des `Form Request` dédiées pour:
  - groups,
  - items,
  - meetings.
- Compléter la validation de certaines actions métier.
- Ajouter davantage de policies:
  - item,
  - group,
  - comment,
  - notification.
- Finaliser la logique du rôle `reader` dans tous les écrans, pas seulement les vues principales.
- Compléter les tests d’autorisations sur:
  - les permissions,
  - les mises à jour,
  - les suppressions,
  - les notifications,
  - le temps réel.
- Activer réellement le broadcasting en environnement de production ou de test réaliste.

### Base de données

- Ajouter éventuellement des contraintes métier plus strictes.
- Vérifier les index si la base grossit.
- Renforcer la cohérence de certaines structures de données.
- Prévoir la montée en charge:
  - files de traitement,
  - workers,
  - persistance des événements si nécessaire.

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

## Statut global

Le projet est déjà bien avancé et le frontend principal est considéré comme terminé pour le périmètre actuel.

Il possède une base fonctionnelle solide sur les 3 couches:
- frontend,
- backend,
- base de données.

Le produit est exploitable, mais il reste encore du travail pour:
- rendre tout le code plus propre et plus homogène,
- compléter les validations,
- compléter les permissions,
- finir la couverture de tests,
- finaliser certaines parties de l’interface,
- rendre le temps réel pleinement opérationnel.

La logique métier la plus importante est déjà en place:
- les workspaces peuvent être créés, partagés, renommés et supprimés,
- les rôles `admin`, `member` et `reader` existent,
- le parcours d’invitation fonctionne aussi pour les nouveaux utilisateurs,
- les vues principales sont connectées aux données réelles,
- le tutoriel d’onboarding est en place.

---

## Ordre de suite recommandé

1. Terminer les tests d’autorisations.
2. Créer les Form Requests manquantes.
3. Sortir la logique métier des routes vers des contrôleurs ou services.
4. Activer et tester le vrai temps réel.
5. Finir le polish de l’interface.
