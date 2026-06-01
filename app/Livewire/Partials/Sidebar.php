<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use Livewire\Attributes\On;

class Sidebar extends Component
{
    public int $myTasksCount = 0;
    public string $newWorkspaceName = '';

    #[On('echo:board-updated,BoardUpdated')]
    #[On('action-converted')]
    #[On('notifications-updated')]
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
        $this->myTasksCount = auth()->user()?->items()->whereNotIn('status', ['done'])->count() ?? 0;
    }

    public function switchWorkspace($workspaceId)
    {
        $user = auth()->user();
        if ($user && $user->workspaces()->where('workspaces.id', $workspaceId)->exists()) {
            $user->update(['current_workspace_id' => $workspaceId]);
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function createWorkspace()
    {
        $this->validate([
            'newWorkspaceName' => 'required|string|max:255'
        ], [
            'newWorkspaceName.required' => 'Le nom est requis.'
        ]);

        $user = auth()->user();
        if (!$user) return;

        try {
            $workspace = \App\Models\Workspace::create([
                'name' => $this->newWorkspaceName,
                'user_id' => $user->id,
                'color' => '#0091CD'
            ]);

            $user->workspaces()->attach($workspace->id, ['role' => 'admin']);
            $user->update(['current_workspace_id' => $workspace->id]);
            $user->refresh();
            
            $this->newWorkspaceName = '';
            
            return $this->redirect(route('dashboard'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création du workspace.');
        }
    }

    public function render()
    {
        return view('livewire.partials.sidebar');
    }
}
