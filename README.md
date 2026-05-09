# 🚀 UniPod-To-Do-List (En cours de développement 🚧)

> **⚠️ AVERTISSEMENT : Ce projet est actuellement en cours de développement et n'est pas encore terminé.**

UniPod-To-Do-List est une application de gestion de tâches et de projets conçue pour offrir une interface moderne, réactive et fluide. Elle permet la création d'espaces de travail (Workspaces), de tableaux (Boards) sous différentes vues (Tableau, Kanban, Calendrier), de groupes de tâches, et de planifier des réunions.

Ce document est un aperçu exhaustif de l'état actuel du projet : ce qui a été fait, comment le tester/modifier, et ce qu'il reste à accomplir.

---

## 🛠️ Stack Technique

- **Backend :** Laravel 11 (PHP 8.3+)
- **Frontend :** Livewire 3, Alpine.js, Tailwind CSS (via Vite)
- **Base de données :** SQLite (par défaut)
- **Authentification :** Laravel Breeze (Session)

---

## ✅ Ce qui a été accompli (État Actuel)

### 🎨 Frontend (Vues & Composants)
- **Intégration du Template & Styles :** Mise en place d'une structure de base avec des composants réutilisables grâce à Laravel Blade et Tailwind CSS. Améliorations récentes des layouts globaux (`app`, `guest`, `navigation`).
- **Routes & Pages (Vues Statiques / Dynamiques) :**
  - Tableau de bord (`/dashboard`)
  - Gestion des Tableaux (`/boards`) avec 3 vues initialisées (Livewire) : `BoardTable`, `BoardKanban`, `BoardCalendar`.
  - Gestion des Réunions (`/meetings`) avec création (`meeting-create`), liste (`MeetingList`), détails (`meeting-show`) et édition (`meeting-edit`).
  - Autres pages préparées (Blade) : Mes Tâches (`my-tasks`), Calendrier global (`calendar`), Rapports (`reports`), Membres (`members`), et Paramètres (`settings`).
- **Composants Livewire (Logique UI) :**
  - Le système de notifications (`Partials/Notifications`) a été enrichi avec une logique backend propre et une vue dédiée pour afficher des alertes interactives.
  - Autres composants pour la réactivité : `Items/ItemPanel`, gestion des boards (comme `BoardTable`).
- **Profil Utilisateur :** Ajout des pages de gestion de profil (édition, mise à jour, suppression) gérées avec les routes Laravel de base.
- **Authentification :** Les vues de connexion, d'inscription, de mot de passe oublié ont été générées et stylisées par Laravel Breeze.

### ⚙️ Backend (Architecture & Logique)
- **Configuration Laravel :** Installation propre de Laravel 11.
- **Authentification & Sécurité :** Système d'authentification Breeze fonctionnel. Les routes principales sont protégées par le middleware `auth`.
- **Routage :** Le fichier `routes/web.php` a été structuré avec des routes groupées et protégées pour toutes les entités principales (Boards, Meetings, Profil, etc.).
- **Livewire Controllers :** Les classes Livewire servent de contrôleurs pour les vues dynamiques, gérant l'état et les événements du frontend (ex: ajout de logique dans `Notifications.php` et `BoardTable.php`).

### 🗄️ Base de Données (Modèles & Migrations)
L'architecture de la base de données relationnelle a été pensée et traduite en migrations Laravel (`database/migrations`) et Modèles Eloquent (`app/Models`) :
- `User` : Utilisateurs du système (Authentification).
- `Workspace` : Espaces de travail regroupant plusieurs tableaux.
- `Board` : Tableaux de bord de tâches.
- `Group` : Groupes ou colonnes de tâches (ex: "À faire", "En cours" pour le Kanban).
- `Item` : Les tâches individuelles.
- `Comment` : Commentaires liés aux tâches.
- `Meeting` : Planification de réunions (Titre, horaires, description).
- `Notification` : Système de notifications pour les utilisateurs.

---

## 🔍 Comment vérifier et modifier le projet

### Prérequis
- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite (ou tout autre SGBD si vous modifiez le `.env`)

### Installation & Lancement
1. Clonez le dépôt.
2. Installez les dépendances PHP : `composer install`
3. Installez les dépendances JS : `npm install`
4. Créez votre fichier d'environnement : `cp .env.example .env`
5. Générez la clé de l'application : `php artisan key:generate`
6. Créez la base de données SQLite (si non existante) et lancez les migrations :
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```
7. Démarrez les serveurs de développement simultanément :
   ```bash
   npm run dev
   php artisan serve
   ```
   *(Note : Assurez-vous d'utiliser `php artisan serve` pour un routage correct avec Laravel, plutôt que `php -S`)*
8. Accédez à l'application via `http://localhost:8000`.

### Modification de l'application
- **Pour le Design & l'UI :** Modifiez les fichiers `.blade.php` dans `resources/views/`. Les classes Tailwind s'appliqueront automatiquement grâce à `npm run dev` (Vite).
- **Pour les interactions réactives :** Modifiez les fichiers PHP dans `app/Livewire/` et leurs vues associées dans `resources/views/livewire/`.
- **Pour la Base de données :** Créez de nouvelles migrations (`php artisan make:migration`), modifiez celles dans `database/migrations/`, et n'oubliez pas d'actualiser avec `php artisan migrate:fresh`. Ajoutez vos relations dans les modèles correspondants situés dans `app/Models/`.

---

## 📝 Ce qu'il reste à faire (To-Do List du Projet)

Bien que la structure de base soit présente, plusieurs fonctionnalités clés doivent encore être implémentées pour rendre l'application pleinement fonctionnelle :

### 🎨 Frontend (À faire)
- [ ] **Kanban Drag & Drop :** Rendre la vue `BoardKanban` pleinement interactive avec SortableJS (déjà présent dans `package.json`).
- [ ] **Dynamisation des vues statiques :** Connecter les pages `Mes Tâches`, `Calendrier`, `Rapports`, et `Membres` aux vraies données Livewire/Eloquent.
- [ ] **UI/UX Polishing :** Ajouter des transitions Alpine.js pour les modales, les menus déroulants (notamment pour les nouvelles notifications).
- [ ] **Éditeur de texte riche :** Intégrer Trix (déjà configuré) pour les descriptions de tâches et les commentaires.

### ⚙️ Backend (À faire)
- [ ] **Logique CRUD complète :** Finaliser la création, lecture, modification et suppression pour toutes les entités (Boards, Groups, Items, Meetings).
- [ ] **Validation des données :** S'assurer que toutes les entrées utilisateurs (Livewire forms) sont strictement validées (Form Requests / Livewire Rules).
- [ ] **Gestion des rôles et autorisations :** Implémenter des Policies Laravel pour restreindre l'accès aux Workspaces et Boards selon le rôle de l'utilisateur (Admin, Membre, Lecteur).
- [ ] **Temps réel (WebSockets) :** Configurer Laravel Echo avec Pusher ou Laravel Reverb pour la mise à jour en temps réel des boards et l'envoi de notifications instantanées.

### 🗄️ Base de Données (À faire)
- [ ] **Seeders & Factories :** Créer des données factices (Faker) pour remplir la base de données rapidement et faciliter les tests de l'interface (ex: générer 50 tâches, 5 boards, etc.).
- [ ] **Optimisation des requêtes :** Prévenir les problèmes "N+1 queries" en utilisant l'Eager Loading (`with()`) dans les contrôleurs Livewire lors de l'affichage des relations (ex: Charger un Board avec ses Groupes et Items).
