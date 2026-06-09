<?php
// app/Livewire/Boards/BoardTable.php

namespace App\Livewire\Boards;

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\Attributes\On;

class BoardTable extends Component
{
    use AuthorizesRequests;

    public Board $board;
    public array $openGroups   = [];
    public array $editingGroup = [];
    public array $editingCell  = [];   // ['item_id' => x, 'field' => 'name']
    public string $newItemName = '';
    public array $bulkSelected = [];
    public bool $bulkMode      = false;
    public string $bulkAction  = '';
    public bool $isCreatingGroup = false;
    public string $newGroupName = '';
    public string $newGroupColor = '#0091CD';

    public function mount(Board $board): void
    {
        $this->authorize('view', $board);
        auth()->user()?->forceFill(['current_workspace_id' => $board->workspace_id])->save();
        $this->board = $board;
        $this->openGroups = $board->groups()->orderBy('order')->pluck('id')->all();
        $this->isCreatingGroup = request()->boolean('createGroup') || session()->has('errors');

        if (session()->has('group_created_id')) {
            $groupId = (int) session('group_created_id');

            if (!in_array($groupId, $this->openGroups, true)) {
                $this->openGroups[] = $groupId;
            }
        }
    }

    #[On('trigger-group-creation')]
    public function showGroupInput(): void
    {
        $this->isCreatingGroup = true;
        $this->dispatch('focus-new-group-input');
    }

    public function cancelGroupCreation(): void
    {
        $this->resetGroupCreationForm();
    }

    public function toggleGroup(int $groupId): void
    {
        if (in_array($groupId, $this->openGroups, true)) {
            $this->openGroups = array_values(array_filter(
                $this->openGroups,
                fn ($id) => $id !== $groupId
            ));

            return;
        }

        $this->openGroups[] = $groupId;
    }

    public function addGroup(): void
    {
        $this->authorize('update', $this->board);

        $validated = Validator::make([
            'name' => $this->newGroupName,
            'color' => $this->newGroupColor,
        ], [
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ])->validate();

        $name = trim($this->newGroupName) ?: 'Nouveau groupe';

        $group = $this->board->groups()->create([
            'name'  => $name,
            'color' => $validated['color'],
            'order' => ((int) $this->board->groups()->max('order')) + 1,
        ]);

        if (!in_array($group->id, $this->openGroups, true)) {
            $this->openGroups[] = $group->id;
        }

        $this->resetGroupCreationForm();
        broadcast(new BoardUpdated($this->board->fresh(), 'group.created', ['group_id' => $group->id]))->toOthers();
    }

    public function deleteGroup(int $groupId): void
    {
        $this->authorize('update', $this->board);
        $group = $this->board->groups()->findOrFail($groupId);
        $group->items()->delete();
        $group->delete();
        
        $this->openGroups = array_values(array_filter(
            $this->openGroups, fn($id) => $id !== $groupId
        ));

        broadcast(new BoardUpdated($this->board->fresh(), 'group.deleted', ['group_id' => $groupId]))->toOthers();
    }

    public function startEditingGroup(int $groupId): void
    {
        $this->editingGroup = ['group_id' => $groupId];
    }

    public function saveGroupName(int $groupId, string $newName): void
    {
        $this->authorize('update', $this->board);
        $validated = Validator::make([
            'name' => $newName,
        ], [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();
        
        $group = $this->board->groups()->findOrFail($groupId);
        $group->update(['name' => trim($validated['name'])]);
        $this->editingGroup = [];
        broadcast(new BoardUpdated($this->board->fresh(), 'group.updated', ['group_id' => $groupId]))->toOthers();
    }

    public function updateGroupColor(int $groupId, string $color): void
    {
        $this->authorize('update', $this->board);
        $validated = Validator::make(compact('color'), [
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ])->validate();

        $group = $this->board->groups()->findOrFail($groupId);
        $group->update(['color' => $validated['color']]);
        broadcast(new BoardUpdated($this->board->fresh(), 'group.updated', ['group_id' => $groupId]))->toOthers();
    }

    // ── Items ─────────────────────────────────────────────────
    public function addItem(int $groupId): void
    {
        $this->authorize('update', $this->board);
        $validated = Validator::make([
            'name' => trim($this->newItemName) ?: 'Nouvelle tâche',
        ], [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        $group = $this->board->groups()->findOrFail($groupId);
        $item  = $group->items()->create([
            'name'    => $validated['name'],
            'status'  => 'todo',
            'priority'=> 'moyenne',
            'order'   => $group->items()->max('order') + 1,
        ]);

        $this->newItemName = '';
        $this->dispatch('item-added', itemId: $item->id);
        broadcast(new BoardUpdated($this->board->fresh(), 'item.created', ['item_id' => $item->id]))->toOthers();
    }

    public function deleteItem(int $itemId): void
    {
        $this->authorize('update', $this->board);
        $item = $this->board->items()->findOrFail($itemId);
        $item->delete();
        broadcast(new BoardUpdated($this->board->fresh(), 'item.deleted', ['item_id' => $itemId]))->toOthers();
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
        $this->authorize('update', $this->board);
        $allowed = ['name', 'deliverable', 'obstacles', 'deadline'];
        if (!in_array($field, $allowed)) return;

        if ($field === 'name' && blank($value)) return;

        Validator::make([$field => $value], [
            'name' => ['required', 'string', 'max:255'],
            'deliverable' => ['nullable', 'string', 'max:5000'],
            'obstacles' => ['nullable', 'string', 'max:5000'],
            'deadline' => ['nullable', 'date'],
        ])->validate();

        $item = $this->board->items()->findOrFail($itemId);
        $item->update([$field => $value ?: null]);
        $this->editingCell = [];
        $this->dispatch('item-updated');
        broadcast(new BoardUpdated($this->board->fresh(), 'item.updated', ['item_id' => $itemId, 'field' => $field]))->toOthers();
    }

    public function updateStatus(int $itemId, string $status): void
    {
        $this->authorize('update', $this->board);
        $allowed = ['done', 'progress', 'todo', 'blocked', 'ongoing'];
        if (!in_array($status, $allowed)) return;
        $item = $this->board->items()->findOrFail($itemId);
        if (!Item::canTransitionStatus($item->status, $status)) return;
        $item->update(['status' => $status]);
        $this->dispatch('item-updated');
        broadcast(new BoardUpdated($this->board->fresh(), 'item.updated', ['item_id' => $itemId, 'field' => 'status']))->toOthers();
    }

    public function updatePriority(int $itemId, string $priority): void
    {
        $this->authorize('update', $this->board);
        $allowed = ['basse', 'moyenne', 'haute', 'critique'];
        if (!in_array($priority, $allowed)) return;
        $item = $this->board->items()->findOrFail($itemId);
        $item->update(['priority' => $priority]);
        broadcast(new BoardUpdated($this->board->fresh(), 'item.updated', ['item_id' => $itemId, 'field' => 'priority']))->toOthers();
    }

    public function updateDeadline(int $itemId, ?string $date): void
    {
        $this->authorize('update', $this->board);

        if (filled($date)) {
            Validator::make(compact('date'), [
                'date' => ['date'],
            ])->validate();
        }

        $item = $this->board->items()->findOrFail($itemId);
        $item->update(['deadline' => $date ?: null]);
        broadcast(new BoardUpdated($this->board->fresh(), 'item.updated', ['item_id' => $itemId, 'field' => 'deadline']))->toOthers();
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
        $this->authorize('update', $this->board);
        if (empty($this->bulkSelected) || empty($this->bulkAction)) return;

        [$type, $value] = explode(':', $this->bulkAction);

        Validator::make([
            'type' => $type,
            'value' => $value,
        ], [
            'type' => ['required', 'in:status,priority'],
            'value' => ['required', 'string'],
        ])->validate();

        if ($type === 'status') {
            $items = $this->board->items()->whereIn('id', $this->bulkSelected)->get();

            foreach ($items as $item) {
                if (!Item::canTransitionStatus($item->status, $value)) {
                    return;
                }
            }
        }

        $this->board->items()->whereIn('id', $this->bulkSelected)->update([$type => $value]);
        $this->bulkSelected = [];
        $this->bulkMode     = false;
        $this->bulkAction   = '';
        $this->dispatch('item-updated');
        broadcast(new BoardUpdated($this->board->fresh(), 'items.bulk-updated', ['field' => $type]))->toOthers();
    }

    public function openItemPanel(int $itemId): void
    {
        $this->dispatch('open-item-panel', itemId: $itemId);
    }

    protected function resetGroupCreationForm(): void
    {
        $this->newGroupName = '';
        $this->newGroupColor = '#0091CD';
        $this->isCreatingGroup = false;
    }

    protected function getListeners(): array
    {
        return [
            "echo-private:boards.{$this->board->id},BoardUpdated" => '$refresh',
        ];
    }

    public function render()
    {
        $groups = $this->board
            ->groups()
            ->with(['items' => fn($q) => $q->with('assignees')->orderBy('order')])
            ->orderBy('order')
            ->get();

        return view('livewire.boards.board-table', [
            'groups' => $groups,
            'taskGroups' => $groups,
        ]);
    }
}
