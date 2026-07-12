# Architecture Temps Réel — Livewire + Reverb

## Vue d'ensemble

Le projet utilise **Laravel Reverb** comme serveur WebSocket pour propager les mises à jour en temps réel. L'architecture repose sur trois couches :

```
Événement métier
  → broadcast (Reverb / Channel publique)
    → Livewire écoute via echo:{channel},{event}
      → Le composant se re-rendenti ($refresh ou refresh())
```

Tout est **zéro polling** : aucune directive `wire:poll` n'existe dans les vues. Le système est 100% événementiel.

---

## 1. Initialisation WebSocket (côté client)

**Fichier actif :** `resources/js/bootstrap.js`

```js
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
```

> **Note :** Le fichier `resources/js/echo.js` existe mais n'est **pas importé** par `app.js`. C'est un vestige.

En production (`.env`), Reverb n'est pas activé par défaut :
```
BROADCAST_CONNECTION=log
```
Tant que cette valeur n'est pas `reverb`, aucune connexion WebSocket n'est établie et les mises à jour en temps réel ne fonctionnent pas.

---

## 2. Les événements broadcastés

Quatre événements implémentent `ShouldBroadcastNow` (synchrone, pas de queue worker) :

| Événement | Canal(s) | Se déclenche quand |
|---|---|---|
| `BoardUpdated` | `boards.{id}` + `workspace.{id}` | Création/modification/suppression d'items, groupes, assignés, commentaires, déplacement kanban |
| `MeetingUpdated` | `workspace.{id}` | Création/modification d'une réunion |
| `NotificationSent` | `users.{id}` | Mention dans un commentaire ou assignation d'une tâche |

**Tous les canaux utilisent `Channel` (public), jamais `PrivateChannel`.**

### Structure `BoardUpdated`

```php
// app/Events/BoardUpdated.php
public function broadcastOn(): array
{
    $channels = [new Channel("boards.{$this->board->id}")];
    if ($this->board->workspace_id) {
        $channels[] = new Channel("workspaces.{$this->board->workspace_id}");
    }
    return $channels;
}

public function broadcastWith(): array
{
    return [
        'board_id'    => $this->board->id,
        'workspace_id'=> $this->board->workspace_id,
        'action'      => $this->action,  // ex: "item.created", "item.moved"
        'payload'     => $this->payload, // données contextuelles
   ];
}
```

L'événement est émis **deux canaux simultanément** :
- `boards.{id}` → pour les composants qui affichent un board spécifique (BoardTable, BoardKanban)
- `workspace.{id}` → pour les composants de niveau workspace (Dashboard, Sidebar, MyTasks)

### Pattern `->toOthers()`

Chaque appel à `broadcast()` est chaîné avec `->toOthers()` :

```php
broadcast(new BoardUpdated($this->board->fresh(), 'item.updated', [...]))->toOthers();
```

Cela évite que l'utilisateur qui déclenche l'action reçoive sa propre notification (il a déjà mis à jour son UI localement). La classe `BroadcastEvent` de base utilise `dontBroadcastToCurrentUser()` : si un socket ID valide est disponible, le serveur exclut cette connexion du broadcast.

---

## 3. Les composants Livewire qui écoutent

Chaque composant définit ses canaux d'écoute via `getListeners()` :

### Composants board (écoute `boards.{boardId}`)

| Composant | Listener | Action |
|---|---|---|
| `BoardTable` | `echo:boards.{id},BoardUpdated` | `$refresh` |
| `BoardKanban` | `echo:boards.{id},BoardUpdated` | `$refresh` |
| `BoardCalendar` | `echo:boards.{id},BoardUpdated` | `$refresh` |

### Composants workspace (écoute `workspace.{workspaceId}`)

| Composant | Listeners |
|---|---|
| `Dashboard` | `echo:workspace.{id},BoardUpdated` → `refresh()` + `echo:workspace.{id},MeetingUpdated` → `refresh()` |
| `MyTasks` | `echo:workspace.{id},BoardUpdated` → `refresh()` |
| `Sidebar` | `echo:workspace.{id},BoardUpdated` → `refresh()` |
| `Notifications` | `echo:users.{userId},NotificationSent` → `refresh()` |

### Événements Livewire locaux (pas de broadcast)

Certains événements ne passent pas par WebSocket et restent dans le navigateur via `$dispatch()` :

| Événement | Déclencheurs | Écouteurs |
|---|---|---|
| `open-item-panel` | BoardKanban, BoardCalendar, BoardTable | `ItemPanel` |
| `notifications-updated` | Notifications (markAsRead) | `Sidebar`, `Partials\Notifications` |
| `workspace-changed` | Navigation | Dashboard, MyTasks, Sidebar |
| `item-updated` | ItemPanel, BoardTable | Parents directs |

---

## 4. Composants qui émettent des broadcasts (22 appels au total)

### BoardTable — 12 appels

C'est le composant **le plus bruyant** en termes de broadcasts :

| Méthode | Action broadcastée |
|---|---|
| `addGroup()` | `group.created` |
| `deleteGroup()` | `group.deleted` |
| `saveGroupName()` | `group.updated` |
| `updateGroupColor()` | `group.updated` |
| `addItem()` | `item.created` |
| `deleteItem()` | `item.deleted` |
| `saveCell()` | `item.updated` |
| `updateStatus()` | `item.updated` |
| `updatePriority()` | `item.updated` |
| `updateDeadline()` | `item.updated` |
| `applyBulkAction()` | `items.bulk-updated` |

### BoardKanban — 2 appels

| Méthode | Action broadcastée |
|---|---|
| `moveItem()` | `item.moved` |
| `addItemToColumn()` | `item.created` |

### ItemPanel — 4 appels

| Méthode | Action broadcastée |
|---|---|
| `saveField()` | `item.updated` |
| `addComment()` | `comment.created` (+ `NotificationSent` si mention) |
| `addAssignee()` | `assignee.added` (+ `NotificationSent`) |
| `removeAssignee()` | `assignee.removed` |

### Contrôleurs — 4 appels

`BoardController` (update, storeGroupe, storeItem) et `MeetingController` (store, update).

---

## 5. ⚠️ Ce qui dégrade l'expérience utilisateur

### 5.1 — `$refresh` massif : re-render complet du DOM du composant

C'est **le problème principal** du projet. La quasi-totalité des écouteurs utilisent `$refresh` :

```php
// BoardTable.php, BoardKanban.php, BoardCalendar.php
protected function getListeners(): array
{
    return [
        "echo:boards.{$this->board->id},BoardUpdated" => '$refresh',
    ];
}
```

**`=$refresh`** signifie : Livewire re-exécute entièrement le `render()`, génère le HTML complet du composant, et le remplace dans le DOM. Pour un board avec 8 groupes et 90+ items, cela provoque :

- Un **flash visuel** perceptible (disparition/réapparition du contenu)
- La **perte de l'état UI local** : tout groupe déplié/refermé, toute cellule en cours d'édition, toute sélection bulk est **réinitialisée** immédiatement
- Un **aller-retour réseau** complet (le composant demande le HTML au serveur via la requête AJAX Livewire interne)
- Des **re-renders en cascade** : quand un `BoardUpdated` est broadcasté, jusqu'à **4 composants** peuvent se rafraîchir simultanément (BoardTable + Dashboard + Sidebar + MyTasks) car le même événement est émis sur deux canaux

### 5.2 — BoardTable : 12 déclencheurs pour un seul composant

Chaque action dans BoardTable (renommer un groupe, changer une deadline, modifier une priorité) :
1. Fait un aller-retour AJAX normal (déclenché par l'utilisateur)
2. Émet un `broadcast()->toOthers()` qui provoque `$refresh` chez **tous les autres utilisateurs**
3. Ce `$refresh` re-render **BoardTable entier** (tous les groupes, tous les items)
4. Le même broadcast sur `workspace.{id}` déclenche aussi `refresh()` sur **Dashboard**, **Sidebar**, et **MyTasks**

Résultat : la moindre action dans la vue table déclenche facilement **5 à 8 requêtes Livewire concurrentes** chez les utilisateurs qui visualisent le même workspace.

### 5.3 — Pas de canal par utilisateur : tout le monde reçoit tout

`BoardUpdated` est émis sur `workspace.{id}`. Dans un workspace avec 5 utilisateurs connectés, une seule modification d'item provoque :
- 4 `$refresh` sur le board (un par autre utilisateur)
- 4 `refresh()` sur le Dashboard
- 4 `refresh()` sur le Sidebar
- 4 `refresh()` sur MyTasks

Soit **16 re-renders serveur** pour une action unique.

### 5.4 — `broadcastWith()` charge le board à chaque fois

```php
// Chaque constructeur BoardUpdated recopie l'objet Board
public function __construct(
    public Board $board,     // ← modèle Eloquent sérialisé entièrement
    public string $action,
    public array $payload = [],
)
```

`$this->board->fresh()` est appelé **avant** le broadcast dans la plupart des émetteurs. Le modèle `Board` (avec ses relations potentiellement chargées par le serializer) est envoyé dans le payload WebSocket. C'est un surcoût inutile dans `broadcastWith()` — seuls `board_id` et `workspace_id` sont réellement utiles.

### 5.5 — Aucune déduplication temporelle

Il n'y a pas de `debounce` ni de `throttle` côté émission. Si un utilisateur change rapidement le statut de 5 items (drag & drop multiple), 5 broadcasts sont envoyés en séquence rapide, chacun déclenchant un `$refresh` complet.

### 5.6 — Login Reverb et latence perçue

Quand Reverb trouve (`BROADCAST_CONNECTION=reverb`), chaque `$refresh` ne passe plus par une simple requête HTTP : le navigateur doit d'abord établir la connexion WebSocket (handshake), puis le temps que le broadcast Server-Sent arrive **et** que Livewire traite le `$refresh` (requête AJAX retour), une latence de 200-500ms peut être perçue. Sans Reverb (`log`), les changements d'un utilisateur ne sont **jamais visibles** par les autres tant qu'ils ne rechargent pas manuellement la page.

---

## 6. Schéma récapitulatif du flux

```
Utilisateur A change le statut d'un item
  │
  ├─→ Livewire AJAX → BoardTable::updateStatus()
  │     ├─→ $item->update(['status' => 'done'])
  │     ├─→ broadcast(new BoardUpdated(..., 'item.updated', ...))->toOthers()
  │     ├─→ Reverb émet sur "boards.{boardId}" et "workspace.{workspaceId}"
  │     └─→ $this->dispatch('item-updated')
  │
  ├─→ [Utilisateur B] Echo reçoit sur "boards.{boardId}"
  │     └─→ Livewire déclenche $refresh
  │           └─→ render() complet → remplace tout le DOM de BoardTable
  │
  ├─→ [Utilisateur B] Echo reçoit sur "workspace.{workspaceId}"
  │     ├─→ Dashboard::refresh()     → render() complet (requêtes SQL)
  │     ├─→ MyTasks::refresh()       → render() complet (requêtes SQL)
  │     └─→ Sidebar::refresh()       → render() complet (requête count)
  │
  └─→ [Utilisateur B] UI se met à jour (flash / re-render)
```

---

## 7. Résumé des canaux et fréquences

```
boards.{boardId}
  Émetteurs  : BoardTable (12), BoardKanban (2), BoardCalendar (0), ItemPanel (4), BoardController (3)
  Écouteurs  : BoardTable $refresh, BoardKanban $refresh, BoardCalendar $refresh
  Fréquence  : Élevée (chaque action CRUD dans la vue board)

workspace.{workspaceId}
  Émetteurs  : Tous les émetteurs ci-dessus (dual-channel)
  Écouteurs  : Dashboard refresh(), MyTasks refresh(), Sidebar refresh()
  Fréquence  : Élevée (propagé automatiquement par BoardUpdated)

users.{userId}
  Émetteurs  : ItemPanel (2 — mention + assignation)
  Écouteurs  : Notifications refresh()
  Fréquence  : Faible
```
