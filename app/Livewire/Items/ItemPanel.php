<?php
// app/Livewire/Items/ItemPanel.php

namespace App\Livewire\Items;

use App\Events\BoardUpdated;
use App\Events\NotificationSent;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;

class ItemPanel extends Component
{
    use AuthorizesRequests;

    public bool   $isOpen       = false;
    public ?Item  $item         = null;
    public string $activeTab    = 'details';
    public string $newComment   = '';
    public string $searchAssignee = '';

    protected $listeners = ['open-item-panel' => 'openPanel'];

    #[On('open-item-panel')]
    public function openPanel(int $itemId): void
    {
        $item = Item::with(['assignees', 'comments.user', 'group.board'])->findOrFail($itemId);
        $this->authorize('view', $item->group->board);
        $this->item      = $item;
        $this->isOpen    = true;
        $this->activeTab = 'details';
    }

    public function closePanel(): void
    {
        $this->isOpen = false;
        $this->item   = null;
    }

    public function saveField(string $field, mixed $value): void
    {
        if (!$this->item) return;
        $this->authorize('update', $this->item->group->board);
        $allowed = ['name', 'status', 'priority', 'deadline', 'description', 'deliverable', 'obstacles'];
        if (!in_array($field, $allowed)) return;

        if (in_array($field, ['description'], true)) {
            $value = $this->sanitizeRichText($value);
        }

        $this->item->update([$field => $value ?: null]);
        $this->item->refresh()->loadMissing(['assignees', 'comments.user', 'group.board']);
        $this->dispatch('item-updated');
        broadcast(new BoardUpdated($this->item->group->board->fresh(), 'item.updated', ['item_id' => $this->item->id, 'field' => $field]))->toOthers();
    }

    public function addComment(): void
    {
        if (!$this->item) return;
        $this->authorize('update', $this->item->group->board);

        $body = $this->sanitizeRichText($this->newComment);

        if (blank(trim(strip_tags($body)))) return;

        $this->item->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $body,
        ]);

        preg_match_all('/@([A-Za-z0-9._-]+)/', strip_tags($body), $mentions);
        $mentionedUsers = User::whereIn('name', $mentions[1] ?? [])
            ->whereKeyNot(auth()->id())
            ->get();

        foreach ($mentionedUsers as $mentionedUser) {
            $notification = Notification::create([
                'type' => 'mention',
                'message' => '<strong>' . e(auth()->user()?->name ?? 'Un membre') . '</strong> vous a mentionné dans <strong>' . e($this->item->name) . '</strong>',
                'action_url' => route('boards.show', $this->item->group->board),
                'action_label' => 'Voir la tâche',
                'user_id' => $mentionedUser->id,
            ]);

            NotificationSent::dispatch($notification);
        }

        $this->newComment = '';
        $this->item->load('comments.user');
        $this->dispatch('trix-clear-comment');
        broadcast(new BoardUpdated($this->item->group->board->fresh(), 'comment.created', ['item_id' => $this->item->id]))->toOthers();
    }

    public function addAssignee(int $userId): void
    {
        if (!$this->item) return;
        $this->authorize('update', $this->item->group->board);
        $workspace = $this->item->group->board->workspace;
        $assignee = User::find($userId);
        abort_unless($assignee && $assignee->belongsToWorkspace($workspace), 422, 'L\'assigné doit appartenir au workspace.');
        $this->item->assignees()->syncWithoutDetaching([$userId]);
        $this->item->load('assignees');
        $this->dispatch('item-updated');

        if ($userId !== auth()->id()) {
            $notification = Notification::create([
                'type' => 'assignment',
                'message' => '<strong>' . e(auth()->user()?->name ?? 'Un membre') . '</strong> vous a assigné la tâche <strong>' . e($this->item->name) . '</strong>',
                'action_url' => route('boards.show', $this->item->group->board),
                'action_label' => 'Voir la tâche',
                'user_id' => $userId,
            ]);

            NotificationSent::dispatch($notification);
        }

        broadcast(new BoardUpdated($this->item->group->board->fresh(), 'assignee.added', ['item_id' => $this->item->id, 'user_id' => $userId]))->toOthers();
    }

    public function removeAssignee(int $userId): void
    {
        if (!$this->item) return;
        $this->authorize('update', $this->item->group->board);
        $this->item->assignees()->detach($userId);
        $this->item->load('assignees');
        $this->dispatch('item-updated');
        broadcast(new BoardUpdated($this->item->group->board->fresh(), 'assignee.removed', ['item_id' => $this->item->id, 'user_id' => $userId]))->toOthers();
    }

    public function getAvailableUsersProperty()
    {
        if (!$this->item) return collect();
        $workspace = $this->item->group->board->workspace;

        return User::whereHas('workspaces', fn ($query) => $query->whereKey($workspace->id))
            ->where('name', 'like', "%{$this->searchAssignee}%")
            ->whereNotIn('id', $this->item->assignees->pluck('id'))
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.items.item-panel');
    }

    protected function sanitizeRichText(mixed $value): ?string
    {
        if (!is_string($value) || blank($value)) {
            return null;
        }

        $cleaned = strip_tags($value, '<p><br><strong><em><a><ul><ol><li><blockquote><code>');

        return Str::of($cleaned)->trim()->toString();
    }
}
