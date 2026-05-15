# 🚀 UniPod-To-Do-List (En cours de développement 🚧)

> **⚠️ AVERTISSEMENT : Ce projet est actuellement en cours de développement et n'est pas encore terminé.**

UniPod-To-Do-List est une application de gestion de tâches et de projets conçue pour offrir une interface moderne, réactive et fluide.

---

## 📊 État Global

Le projet a déjà une base fonctionnelle solide sur les 3 couches: frontend, backend et base de données. Les boards, tâches, groupes, meetings, notifications et vues principales existent, avec une bonne partie du flux produit déjà branchée. Le travail récent a surtout consolidé la cohérence entre les vues, sécurisé le backend, et rendu plusieurs écrans réellement dynamiques.

---

## ✅ Ce qui a été fait

### 🎨 Frontend

- **Vues boards opérationnelles :**
    - Tableau, Kanban, Calendrier board sont branchés aux vraies données.
    - Ajout de tâche unifié entre les 3 vues.
    - Ajout de groupe fonctionnel.
    - Panneau latéral de tâche fonctionnel.
- **Dashboard et onglets principaux dynamisés :**
    - Dashboard
    - Calendrier
    - Rapports
    - Membres
    - Notifications
    - Paramètres
- **UI/UX :**
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
