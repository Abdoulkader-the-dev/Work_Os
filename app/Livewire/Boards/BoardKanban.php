<?php
// app/Livewire/Boards/BoardKanban.php

namespace App\Livewire\Boards;

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class BoardKanban extends Component
{
    use AuthorizesRequests;

    public Board $board;

    public array $columns = [
        'todo'     => ['label' => 'Non commencé', 'color' => '#a3a39f'],
        'progress' => ['label' => 'En cours',      'color' => '#0091CD'],
        'ongoing'  => ['label' => 'Continu',        'color' => '#f97316'],
        'blocked'  => ['label' => 'Bloqué',         'color' => '#ef4444'],
        'done'     => ['label' => 'Achevé',         'color' => '#22c55e'],
    ];

    public function mount(Board $board): void
    {
        $this->authorize('view', $board);
        auth()->user()?->forceFill(['current_workspace_id' => $board->workspace_id])->save();
        $this->board = $board;
    }

    public function moveItem(int $itemId, string $status, array $targetIds = [], array $sourceIds = []): void
    {
        $this->authorize('update', $this->board);
        $allowed = ['todo', 'progress', 'ongoing', 'blocked', 'done'];
        if (!in_array($status, $allowed)) return;

        $item = $this->board->items()->findOrFail($itemId);
        if (!Item::canTransitionStatus($item->status, $status)) return;
        $item->update(['status' => $status]);

        foreach (array_values($targetIds) as $index => $id) {
            $targetItem = $this->board->items()->whereKey($id)->first();

            if (!$targetItem || !Item::canTransitionStatus($targetItem->status, $status)) {
                return;
            }

            $targetItem->update([
                'status' => $status,
                'order'  => $index + 1,
            ]);
        }

        if (!empty($sourceIds)) {
            foreach (array_values($sourceIds) as $index => $id) {
                $this->board->items()->whereKey($id)->update([
                    'order' => $index + 1,
                ]);
            }
        }

        broadcast(new BoardUpdated($this->board->fresh(), 'item.moved', ['item_id' => $itemId, 'status' => $status]))->toOthers();
    }

    public function openItemPanel(int $itemId): void
    {
        $this->dispatch('open-item-panel', itemId: $itemId);
    }

    public function addItemToColumn(string $status): void
    {
        $this->authorize('update', $this->board);
        if (!in_array($status, Item::allowedStatuses(), true)) return;
        $firstGroup = $this->board->groups()->orderBy('order')->first();
        if (!$firstGroup) {
            $firstGroup = $this->board->groups()->create([
                'name'  => 'Général',
                'color' => '#0091CD',
                'order' => 1,
            ]);
        }
        $item = $firstGroup->items()->create([
            'name'   => 'Nouvelle tâche',
            'status' => $status,
            'order'  => $firstGroup->items()->where('status', $status)->max('order') + 1,
        ]);
        $this->dispatch('open-item-panel', itemId: $item->id);
        broadcast(new BoardUpdated($this->board->fresh(), 'item.created', ['item_id' => $item->id]))->toOthers();
    }

    protected function getListeners(): array
    {
        return [
            "echo:boards.{$this->board->id},BoardUpdated" => '$refresh',
        ];
    }

    public function render()
    {
        $items = $this->board
            ->items()
            ->with('assignees')
            ->orderBy('order')
            ->get()
            ->groupBy('status');

        $groups = $this->board
            ->groups()
            ->orderBy('order')
            ->get();

        return view('livewire.boards.board-kanban', [
            'items' => $items,
            'groups' => $groups,
        ]);
    }
}
