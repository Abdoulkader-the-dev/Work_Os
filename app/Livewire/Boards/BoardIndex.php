<?php

namespace App\Livewire\Boards;

use App\Models\Board;
use Livewire\Component;

class BoardIndex extends Component
{
    public function refresh() {}

    protected function getListeners(): array
    {
        $workspaceId = auth()->user()?->activeWorkspace?->id;

        $listeners = [
            'workspace-changed' => 'refresh',
        ];

        if ($workspaceId) {
            $listeners["echo-private:workspaces.{$workspaceId},BoardUpdated"] = 'refresh';
        }

        return $listeners;
    }

    public function render()
    {
        $workspace = auth()->user()?->activeWorkspace;
        $boards = Board::query()
            ->where('workspace_id', $workspace?->id)
            ->withCount('items')
            ->get();

        return view('livewire.boards.board-index', [
            'boards' => $boards,
            'workspace' => $workspace,
            'canManageBoards' => auth()->user()?->can('create', Board::class),
        ]);
    }
}
