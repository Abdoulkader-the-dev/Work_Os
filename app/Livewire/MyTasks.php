<?php

namespace App\Livewire;

use Livewire\Component;

class MyTasks extends Component
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

        $items = auth()->user()?->items()
            ->with('group.board')
            ->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id))
            ->orderByRaw("case when status = 'blocked' then 0 when status = 'progress' then 1 when status = 'todo' then 2 when status = 'ongoing' then 3 when status = 'done' then 4 else 5 end")
            ->orderBy('deadline')
            ->get() ?? collect();

        return view('livewire.my-tasks', [
            'items' => $items,
            'workspace' => $workspace,
        ]);
    }
}
