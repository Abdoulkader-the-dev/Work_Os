<?php
// app/Livewire/Meetings/MeetingEditor.php

namespace App\Livewire\Meetings;

use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use App\Models\Meeting;
use Livewire\Component;

class MeetingEditor extends Component
{
    // ── Méta ──────────────────────────────────────────────
    public ?int    $meetingId   = null;
    public string  $title       = '';
    public string  $date        = '';
    public string  $attendeeInput = '';
    public array   $attendees   = [];

    // ── Sections ──────────────────────────────────────────
    public array $bilan           = [''];
    public array $recommendations = [''];
    public array $actions         = [];   // [['text','assignee_id','deadline','converted','item_id']]

    // ── UI state ──────────────────────────────────────────
    public bool   $saved        = false;
    public string $saveMessage  = '';

    public function mount(?Meeting $meeting = null): void
    {
        $this->date = now()->format('Y-m-d');

        if ($meeting && $meeting->exists) {
            $this->meetingId       = $meeting->id;
            $this->title           = $meeting->title;
            $this->date            = $meeting->date->format('Y-m-d');
            $this->attendees       = $meeting->attendees ?? [];
            $this->bilan           = $meeting->bilan ?: [''];
            $this->recommendations = $meeting->recommendations ?: [''];
            $this->actions         = $meeting->actions ?: [];
        }

        if (empty($this->actions)) {
            $this->actions = [['text' => '', 'assignee_id' => null, 'deadline' => '', 'converted' => false, 'item_id' => null]];
        }
    }

    // ── Attendees ─────────────────────────────────────────
    public function addAttendee(): void
    {
        $name = trim($this->attendeeInput);
        if ($name && !in_array($name, $this->attendees)) {
            $this->attendees[] = $name;
        }
        $this->attendeeInput = '';
    }

    public function removeAttendee(int $index): void
    {
        array_splice($this->attendees, $index, 1);
    }

    // ── Bilan ─────────────────────────────────────────────
    public function addBilanPoint(): void
    {
        $this->bilan[] = '';
    }

    public function removeBilanPoint(int $index): void
    {
        array_splice($this->bilan, $index, 1);
        if (empty($this->bilan)) $this->bilan = [''];
    }

    // ── Recommandations ───────────────────────────────────
    public function addRecommendation(): void
    {
        $this->recommendations[] = '';
    }

    public function removeRecommendation(int $index): void
    {
        array_splice($this->recommendations, $index, 1);
        if (empty($this->recommendations)) $this->recommendations = [''];
    }

    // ── Actions ───────────────────────────────────────────
    public function addAction(): void
    {
        $this->actions[] = [
            'text'        => '',
            'assignee_id' => null,
            'deadline'    => '',
            'converted'   => false,
            'item_id'     => null,
        ];
    }

    public function removeAction(int $index): void
    {
        array_splice($this->actions, $index, 1);
        if (empty($this->actions)) $this->addAction();
    }

    // ── Conversion action → tâche ─────────────────────────
    public function convertActionToTask(int $index): void
    {
        $action = $this->actions[$index] ?? null;
        if (!$action || empty(trim($action['text'] ?? ''))) return;
        if ($action['converted'] ?? false) return;

        // Trouver le premier board du workspace courant
        $board = Board::first(); // adapter selon le contexte workspace
        if (!$board) return;

        $group = $board->groups()->firstOrCreate(
            ['name' => 'Réunions'],
            ['color' => '#0091CD', 'order' => 99]
        );

        $item = $group->items()->create([
            'name'     => $action['text'],
            'status'   => 'todo',
            'priority' => 'moyenne',
            'deadline' => $action['deadline'] ?: null,
        ]);

        if (!empty($action['assignee_id'])) {
            $item->assignees()->attach($action['assignee_id']);
        }

        $this->actions[$index]['converted'] = true;
        $this->actions[$index]['item_id']   = $item->id;

        $this->saveMeeting();
        $this->dispatch('action-converted', itemId: $item->id, itemName: $item->name);
    }

    // ── Sauvegarde ────────────────────────────────────────
    public function saveMeeting(): void
    {
        $this->validate([
            'title' => 'required|min:2',
            'date'  => 'required|date',
        ]);

        $data = [
            'title'           => $this->title,
            'date'            => $this->date,
            'attendees'       => array_filter($this->attendees),
            'bilan'           => array_filter($this->bilan),
            'recommendations' => array_filter($this->recommendations),
            'actions'         => $this->actions,
            'user_id'         => auth()->id(),
        ];

        if ($this->meetingId) {
            Meeting::findOrFail($this->meetingId)->update($data);
        } else {
            $meeting = Meeting::create($data);
            $this->meetingId = $meeting->id;
        }

        $this->saved       = true;
        $this->saveMessage = 'Compte rendu sauvegardé';
        $this->dispatch('meeting-saved');

        // Reset feedback après 3s
        $this->js("setTimeout(() => \$wire.set('saved', false), 3000)");
    }

    public function getAvailableUsersProperty()
    {
        return \App\Models\User::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.meetings.meeting-editor');
    }
}