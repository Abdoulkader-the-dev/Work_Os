<?php
// app/Livewire/Meetings/MeetingList.php

namespace App\Livewire\Meetings;

use App\Models\Meeting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search    = '';
    public string $sortField = 'date';
    public string $sortDir   = 'desc';

    #[On('workspace-changed')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    protected function getListeners(): array
    {
        $workspaceId = auth()->user()?->activeWorkspace?->id;

        $listeners = [
            'workspace-changed' => 'refresh',
        ];

        if ($workspaceId) {
            $listeners["echo-private:workspaces.{$workspaceId},MeetingUpdated"] = 'refresh';
        }

        return $listeners;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sortDir   = $this->sortField === $field && $this->sortDir === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function deleteMeeting(int $id): void
    {
        $meeting = Meeting::findOrFail($id);
        $this->authorize('delete', $meeting);
        $meeting->delete();
    }

    public function render()
    {
        $this->authorize('viewAny', Meeting::class);
        $workspace = auth()->user()?->activeWorkspace;
        abort_unless($workspace, 422, 'Aucun workspace actif.');

        $meetings = Meeting::query()
            ->where(function ($query) use ($workspace) {
                $query->where('workspace_id', $workspace->id)
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('workspace_id')
                            ->where('user_id', auth()->id());
                    });
            })
            ->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('attendees', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate(12);

        return view('livewire.meetings.meeting-list', compact('meetings'));
    }
}
