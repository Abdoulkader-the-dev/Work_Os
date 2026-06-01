<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use Livewire\Attributes\On;

class Sidebar extends Component
{
    public int $myTasksCount = 0;

    #[On('echo:board-updated,BoardUpdated')]
    #[On('action-converted')]
    #[On('notifications-updated')]
    #[On('workspace-changed')]
    public function refresh()
    {
        $this->updateCounts();
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
