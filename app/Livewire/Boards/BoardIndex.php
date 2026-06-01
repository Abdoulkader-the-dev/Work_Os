<?php

namespace App\Livewire\Boards;

use App\Models\Board;
use Livewire\Component;
use Livewire\Attributes\On;

class BoardIndex extends Component
{
    #[On('echo:board-updated,BoardUpdated')]
    public function refresh() {}

    public function render()
    {
        $workspace = auth()->user()?->activeWorkspace;
        $boards = Board::query()
            ->where('workspace_id', $workspace?->id)
            ->withCount('items')
            ->get();

        return view('livewire.boards.board-index', [
            'boards' => $boards,
            'canManageBoards' => auth()->user()?->can('create', Board::class),
        ]);
    }
}
