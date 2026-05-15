<?php
// app/Livewire/Boards/BoardKanban.php

namespace App\Livewire\Boards;

use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use Livewire\Component;

class BoardKanban extends Component
{
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
        $this->board = $board;
    }

    public function moveItem(int $itemId, string $status, array $targetIds = [], array $sourceIds = []): void
    {
        $allowed = ['todo', 'progress', 'ongoing', 'blocked', 'done'];
        if (!in_array($status, $allowed)) return;

        $item = Item::findOrFail($itemId);
        $item->update(['status' => $status]);

        foreach (array_values($targetIds) as $index => $id) {
            Item::whereKey($id)->update([
                'status' => $status,
                'order'  => $index + 1,
            ]);
        }

        if (!empty($sourceIds)) {
            foreach (array_values($sourceIds) as $index => $id) {
                Item::whereKey($id)->update([
                    'order' => $index + 1,
                ]);
            }
        }
    }

    public function openItemPanel(int $itemId): void
    {
        $this->dispatch('open-item-panel', itemId: $itemId);
    }

    public function addItemToColumn(string $status): void
    {
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
