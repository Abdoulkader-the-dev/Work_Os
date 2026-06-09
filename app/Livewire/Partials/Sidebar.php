<?php

namespace App\Livewire\Partials;

use Livewire\Component;

class Sidebar extends Component
{
    public int $myTasksCount = 0;

    public function refresh()
    {
        $this->updateCounts();
    }

    protected function getListeners(): array
    {
        $workspaceId = auth()->user()?->activeWorkspace?->id;

        $listeners = [
            'action-converted' => 'refresh',
            'notifications-updated' => 'refresh',
            'workspace-changed' => 'refresh',
        ];

        if ($workspaceId) {
            $listeners["echo:workspaces.{$workspaceId},BoardUpdated"] = 'refresh';
        }

        return $listeners;
    }

    public function mount()
    {
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $workspace = auth()->user()?->activeWorkspace;

        $this->myTasksCount = auth()->user()?->items()
            ->whereNotIn('status', ['done'])
            ->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id))
            ->count() ?? 0;
    }

    public function render()
    {
        return view('livewire.partials.sidebar');
    }
}
