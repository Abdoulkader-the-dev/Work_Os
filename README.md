# 🚀 UniPod-To-Do-List (En cours de développement 🚧)

> **⚠️ AVERTISSEMENT : Ce projet est actuellement en cours de développement et n'est pas encore terminé.**

UniPod-To-Do-List est une application de gestion de tâches et de projets conçue pour offrir une interface moderne, réactive et fluide.

---

## 📊 État Global

Le projet a déjà une base fonctionnelle solide sur les 3 couches: frontend, backend et base de données. Les boards, tâches, groupes, meetings, notifications et vues principales existent, avec une bonne partie du flux produit déjà branchée. Le travail récent a surtout consolidé la cohérence entre les vues, sécurisé le backend, et rendu plusieurs écrans réellement dynamiques.

---

## 🏗️ Architecture du Projet

Ce projet suit l'architecture MVC (Modèle-Vue-Contrôleur) classique de Laravel, enrichie avec **Livewire** pour la réactivité frontend sans JavaScript lourd, et **Alpine.js** pour les interactions UI légères. 

### 📂 Structure des Dossiers & Fichiers

- **`app/`** : C'est le cœur de l'application backend.
  - **`Models/`** : Définit la structure des données et les relations (ex: `User`, `Workspace`, `Board`, `Group`, `Item`, `Meeting`, `Notification`, `Comment`).
    - *Logique* : Un `Workspace` contient des `User` (membres) et des `Board`. Un `Board` contient des `Group`, qui contiennent des `Item` (tâches).
  - **`Livewire/`** : Contient les contrôleurs des composants réactifs (ex: `Boards/BoardTable`, `BoardKanban`, `BoardCalendar`).
    - *Interaction* : Ces fichiers PHP gèrent l'état de la vue en temps réel. Ils interceptent les actions de l'utilisateur sur la page et mettent à jour le HTML de manière dynamique sans rechargement.
  - **`Http/Controllers/`** : Contrôleurs classiques pour les vues non-Livewire (ex: `ProfileController` pour la gestion du profil utilisateur).
  - **`Http/Requests/`** : Classes de validation des formulaires (ex: `BoardStoreRequest`, `BoardUpdateRequest`) qui sécurisent les données entrantes.
  - **`Events/`** : Événements diffusés via Laravel Echo (ex: `BoardUpdated`), permettant de mettre à jour le frontend en temps réel via WebSockets.

- **`routes/`** : 
  - **`web.php`** : Définit toutes les URLs de l'application. Associe une URL (ex: `/boards`) à une vue Blade ou à un composant Livewire. Gère également le middleware `auth` pour bloquer les utilisateurs non connectés.

- **`resources/`** : 
  - **`views/`** : Fichiers `.blade.php` responsables de l'affichage HTML.
    - **`pages/`** : Les vues principales (Dashboard, Calendrier, Meetings).
    - **`livewire/`** : Les templates associés aux composants de `app/Livewire/`.
    - **`components/` & `layouts/`** : Éléments réutilisables (Topbars, sidebars, modales).
  - **`css/` & `js/`** : Contiennent le style Tailwind CSS et les scripts d'initialisation (notamment Alpine.js et Laravel Echo). L'entrée principale est `app.js`.

- **`database/`** :
  - **`migrations/`** : Fichiers PHP qui créent et modifient les tables de la base de données (le schéma relationnel).
  - **`seeders/` & `factories/`** : Scripts pour générer de fausses données (mocks) très utiles pour le développement et tester l'UI.

- **`public/`** : Dossier exposé au web, contenant l'`index.php` (point d'entrée) et les assets compilés (via Vite).

### ⚙️ Logique et Flux de Données

1. **Requête Utilisateur** : L'utilisateur navigue vers une URL (ex: `/boards/{board}`). 
2. **Routage (`web.php`)** : Laravel intercepte l'URL et appelle le composant Livewire `BoardTable::class`.
3. **Logique Backend (`app/Livewire/Boards/BoardTable.php`)** : Le composant récupère le board, ses groupes et ses tâches (`Item`) via les modèles Éloquent correspondants.
4. **Rendu Frontend (`resources/views/livewire/boards/board-table.blade.php`)** : Le composant génère le HTML en utilisant les directives Blade.
5. **Interactions UI (`Alpine.js`)** : Les modales (ex: création de tâche), les dropdowns, et les panneaux latéraux (sidepanels) s'ouvrent ou se ferment instantanément sans appel serveur grâce à des directives `x-data`, `x-show`.
6. **Soumission de Formulaire / Action** : Lorsqu'une tâche est modifiée :
   - Si c'est en Livewire, la méthode PHP correspondante est appelée.
   - Si c'est une route standard (ex: `POST /boards/{board}/items`), le contrôleur dans `web.php` valide la requête, met à jour la base de données, et dispatche l'événement `BoardUpdated`.
7. **Temps Réel (`Laravel Echo`)** : Si un autre utilisateur est sur le même board, Laravel Echo écoute l'événement `BoardUpdated` via WebSockets (Reverb/Pusher) et met à jour son interface instantanément.

---

## ✅ Ce qui a été fait

### 🎨 Frontend

- **Vues boards opérationnelles :**
    - Tableau, Kanban, Calendrier board sont branchés aux vraies données.
    - Ajout de tâche unifié entre les 3 vues.
    - Ajout de groupe fonctionnel.
    - Panneau latéral de tâche fonctionnel.
- **Dashboard et onglets principaux dynamisés :**
    - Dashboard avec état vide (Empty State) personnalisé pour les nouveaux utilisateurs.
    - Calendrier, Rapports, Membres, Notifications, Paramètres.
- **UI/UX & Feedback visuel :**
    - **Barre de progression globale** : Ajout d'une barre de chargement en haut de page lors des transitions `wire:navigate`.
    - **États de chargement** : Spinners et indicateurs visuels ajoutés sur les actions critiques (création/switch de workspace).
    - Transitions Alpine sur le panneau tâche.
    - Transitions sur le dropdown notifications.
    - Animation du modal de création board.
    - Gestion `x-cloak` pour éviter les flashes.
- **Texte riche :**
    - Trix intégré pour la description des tâches.
    - Trix intégré pour les commentaires.
    - Trix intégré dans le formulaire de création de tâche.
- **Gestion board côté UI :**
    - Création, modification, suppression.
    - Contrôle d’affichage selon permissions.

### ⚙️ Backend

- **Gestion des Workspaces :**
    - Création et switch de workspace avec redirection automatique vers le dashboard.
    - Logique de "Workspace actif" persistante en base de données.
    - Rafraîchissement automatique de l'état utilisateur après modifications.
- **CRUD partiellement finalisé :**
    - Board : create, read, update, delete disponibles.
    - Group : create, update partiel, delete.
    - Item : create, update, delete, bulk update, move en kanban.
    - Meeting : create, read, update, delete via Livewire.
- **Validation :**
    - `BoardStoreRequest`
    - `BoardUpdateRequest`
    - Validations renforcées dans plusieurs composants Livewire : `BoardTable`, `BoardKanban`, `BoardCalendar`.
- **Temps réel :**
    - Temps réel branché sur les vues board via Echo.
    - Notifications instantanées minimales sur assignation / mention.

### 🗄️ Base de données

- **Schéma principal déjà présent :**
    - `users`, `workspaces`, `workspace_user`, `boards`, `groups`, `items`, `item_user`, `comments`, `meetings`, `notifications`.
- **Évolution récente :**
    - Ajout de description sur les items.
- **Seed cohérent :**
    - `users`, `workspace`, rôles, `boards`, `groupes`, `tâches`, `meetings`. (Seeders plus avancés à faire).
- **Search globale topbar :**
    - Actuellement vraie recherche transverse.
- **Notifications :**
    - Meilleure granularité visuelle (redirection vers vues lourdes en inline styles qui méritent une harmonisation CSS).
    - Certains dropdowns utilisent encore plusieurs patterns différents.

---

## 📝 Ce qu'il reste à faire (To-Do List du Projet)

### ⚙️ Backend

- **CRUD encore à finaliser complètement pour toutes les entités :**
    - **Board :** Manque probablement des tests dédiés d’update/delete.
    - **Group :** Pas encore de Form Request dédiée, pas encore de routes REST complètes.
    - **Item :** Pas encore de Form Request dédiée, logique encore répartie entre route closure + Livewire.
    - **Meeting :** Validation encore inline dans Livewire, pas externalisée.
- **Validation avancée :**
    - Créer des objets dédiés pour : `GroupStore/UpdateRequest`, `ItemStore/UpdateRequest`, `MeetingStore/UpdateRequest`.
    - Centraliser certaines regex/règles métier.
- **Autorisations :**
    - Rôle reader prévu conceptuellement, mais pas encore complètement déroulé dans seed + UI + tests.
    - Pas encore de policy explicite pour `Item`, `Group`, `Comment`, `Notification`.
- **Temps réel :**
    - Le code est prêt, mais l’environnement tourne encore sur broadcasting = log.
    - Il faut activer réellement : soit Laravel Reverb, soit Pusher.
    - Il faut ensuite tester le flux temps réel en conditions réelles.
- **Architecture :**
    - Plusieurs routes utilisent encore des closures.
    - Une partie de la logique métier gagnerait à être déplacée vers : controllers, actions/services, form requests, notifications Laravel natives.
- **Tests :**
    - Pas encore de couverture solide sur : policies, rôles admin/member/reader, update/delete boards/groups/items/meetings, broadcasting / notifications temps réel.

### 🗄️ Base de données

- **À améliorer :**
    - Ajouter éventuellement des contraintes plus strictes sur certains champs métier.
    - Vérifier les index utiles si la volumétrie augmente.
    - Potentiellement normaliser davantage certaines structures JSON de meetings si besoin d’analytics avancée.
- **Temps réel / notifications :**
    - Si montée en charge, il faudra penser aux files (queues), aux workers, et à la persistance associée.

---

## 🚀 Recommandation de suite

L’ordre le plus logique maintenant est :

1. Ajouter les tests d’autorisation admin / member / reader.
2. Créer les Form Requests pour Group, Item, Meeting.
3. Sortir la logique métier des closures/routes vers des controllers ou services.
4. Activer réellement Reverb ou Pusher.
5. Finir le polish UI restant.
