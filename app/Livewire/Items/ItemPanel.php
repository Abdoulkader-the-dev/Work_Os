<?php
// app/Livewire/Items/ItemPanel.php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;

class ItemPanel extends Component
{
    public bool   $isOpen       = false;
    public ?Item  $item         = null;
    public string $activeTab    = 'details';
    public string $newComment   = '';
    public string $searchAssignee = '';

    protected $listeners = ['open-item-panel' => 'openPanel'];

    #[On('open-item-panel')]
    public function openPanel(int $itemId): void
    {
        $this->item      = Item::with(['assignees', 'comments.user', 'group.board'])->findOrFail($itemId);
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
        $allowed = ['name', 'status', 'priority', 'deadline', 'deliverable', 'obstacles'];
        if (!in_array($field, $allowed)) return;
        $this->item->update([$field => $value ?: null]);
        $this->dispatch('item-updated');
    }

    public function addComment(): void
    {
        if (!$this->item || empty(trim($this->newComment))) return;
        $this->item->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $this->newComment,
        ]);
        $this->newComment = '';
        $this->item->load('comments.user');
    }

    public function addAssignee(int $userId): void
    {
        if (!$this->item) return;
        $this->item->assignees()->syncWithoutDetaching([$userId]);
        $this->item->load('assignees');
        $this->dispatch('item-updated');
    }

    public function removeAssignee(int $userId): void
    {
        if (!$this->item) return;
        $this->item->assignees()->detach($userId);
        $this->item->load('assignees');
        $this->dispatch('item-updated');
    }

    public function getAvailableUsersProperty()
    {
        if (!$this->item) return collect();
        return User::where('name', 'like', "%{$this->searchAssignee}%")
            ->whereNotIn('id', $this->item->assignees->pluck('id'))
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.items.item-panel');
    }
}