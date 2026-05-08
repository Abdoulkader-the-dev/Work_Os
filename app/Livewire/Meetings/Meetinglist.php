<?php
// app/Livewire/Meetings/MeetingList.php

namespace App\Livewire\Meetings;

use App\Models\Meeting;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingList extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $sortField = 'date';
    public string $sortDir   = 'desc';

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
        Meeting::findOrFail($id)->delete();
    }

    public function render()
    {
        $meetings = Meeting::query()
            ->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('attendees', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate(12);

        return view('livewire.meetings.meeting-list', compact('meetings'));
    }
}