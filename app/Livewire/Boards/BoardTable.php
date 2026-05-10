<?php
// app/Livewire/Boards/BoardTable.php

namespace App\Livewire\Boards;

use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use Livewire\Component;
use Livewire\Attributes\On;

class BoardTable extends Component
{
    public Board $board;
    public array $openGroups   = [];
    public array $editingCell  = [];   // ['item_id' => x, 'field' => 'name']
    public string $newItemName = '';
    public array $bulkSelected = [];
    public bool $bulkMode      = false;
    public string $bulkAction  = '';

    public function mount(Board $board): void
    {
        $this->board      = $board;
        $this->openGroups = $board->groups()->pluck('id')->toArray();
    }

    // ── Groupes ──────────────────────────────────────────────
    public function toggleGroup(int $groupId): void
    {
        if (in_array($groupId, $this->openGroups)) {
            $this->openGroups = array_values(array_filter(
                $this->openGroups, fn($id) => $id !== $groupId
            ));
        } else {
            $this->openGroups[] = $groupId;
        }
    }

    public function addGroup(): void
    {
        $group = $this->board->groups()->create([
            'name'  => 'Nouveau groupe',
            'color' => '#0091CD',
            'order' => $this->board->groups()->max('order') + 1,
        ]);
        $this->openGroups[] = $group->id;
    }

    // ── Items ─────────────────────────────────────────────────
    public function addItem(int $groupId): void
    {
        if (empty(trim($this->newItemName))) {
            $this->newItemName = 'Nouvelle tâche';
        }

        $group = Group::findOrFail($groupId);
        $item  = $group->items()->create([
            'name'    => $this->newItemName,
            'status'  => 'todo',
            'priority'=> 'moyenne',
            'order'   => $group->items()->max('order') + 1,
        ]);

        $this->newItemName = '';
        $this->dispatch('item-added', itemId: $item->id);
    }

    public function deleteItem(int $itemId): void
    {
        Item::findOrFail($itemId)->delete();
    }

    // ── Édition inline ────────────────────────────────────────
    public function startEditing(int $itemId, string $field): void
    {
        $this->editingCell = ['item_id' => $itemId, 'field' => $field];
    }

    public function stopEditing(): void
    {
        $this->editingCell = [];
    }

    public function saveCell(int $itemId, string $field, mixed $value): void
    {
        $allowed = ['name', 'deliverable', 'obstacles', 'deadline'];
        if (!in_array($field, $allowed)) return;

        Item::findOrFail($itemId)->update([$field => $value]);
        $this->editingCell = [];
        $this->dispatch('item-updated');
    }

    public function updateStatus(int $itemId, string $status): void
    {
        $allowed = ['done', 'progress', 'todo', 'blocked', 'ongoing'];
        if (!in_array($status, $allowed)) return;
        Item::findOrFail($itemId)->update(['status' => $status]);
        $this->dispatch('item-updated');
    }

    public function updatePriority(int $itemId, string $priority): void
    {
        $allowed = ['basse', 'moyenne', 'haute', 'critique'];
        if (!in_array($priority, $allowed)) return;
        Item::findOrFail($itemId)->update(['priority' => $priority]);
    }

    public function updateDeadline(int $itemId, string $date): void
    {
        Item::findOrFail($itemId)->update(['deadline' => $date ?: null]);
    }

    // ── Bulk actions ──────────────────────────────────────────
    public function toggleBulkSelect(int $itemId): void
    {
        if (in_array($itemId, $this->bulkSelected)) {
            $this->bulkSelected = array_values(array_filter(
                $this->bulkSelected, fn($id) => $id !== $itemId
            ));
        } else {
            $this->bulkSelected[] = $itemId;
        }
        $this->bulkMode = !empty($this->bulkSelected);
    }

    public function applyBulkAction(): void
    {
        if (empty($this->bulkSelected) || empty($this->bulkAction)) return;

        [$type, $value] = explode(':', $this->bulkAction);

        Item::whereIn('id', $this->bulkSelected)->update([$type => $value]);
        $this->bulkSelected = [];
        $this->bulkMode     = false;
        $this->bulkAction   = '';
        $this->dispatch('item-updated');
    }

    public function openItemPanel(int $itemId): void
    {
        $this->dispatch('open-item-panel', itemId: $itemId);
    }

    public function render()
    {
        $groups = $this->board
            ->groups()
            ->with(['items' => fn($q) => $q->with('assignees')->orderBy('order')])
            ->orderBy('order')
            ->get();

        return view('livewire.boards.board-table', compact('groups'));
    }
}