<?php
// app/Livewire/Meetings/MeetingEditor.php

namespace App\Livewire\Meetings;

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use App\Models\Meeting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class MeetingEditor extends Component
{
    use AuthorizesRequests;
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

    public function mount(?int $meetingId = null): void
    {
        if ($meetingId) {
            $meeting = Meeting::findOrFail($meetingId);
            $this->authorize('update', $meeting);

            $this->meetingId       = $meeting->id;
            $this->title           = $meeting->title;
            $this->date            = $meeting->date->format('Y-m-d');
            $this->attendees       = $meeting->attendees ?? [];
            $this->bilan           = $meeting->bilan ?: [''];
            $this->recommendations = $meeting->recommendations ?: [''];
            $this->actions         = $meeting->actions ?: [];
        } else {
            $this->authorize('create', Meeting::class);
            $this->date = now()->format('Y-m-d');
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
        $this->authorize($this->meetingId ? 'update' : 'create', $this->meetingId ? Meeting::findOrFail($this->meetingId) : Meeting::class);
        $action = $this->actions[$index] ?? null;
        if (!$action || empty(trim($action['text'] ?? ''))) return;
        if ($action['converted'] ?? false) return;

        $board = Board::query()
            ->where('workspace_id', auth()->user()?->activeWorkspace?->id)
            ->first();
        if (!$board) return;

        abort_unless(auth()->user()?->can('update', $board), 403);

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
        BoardUpdated::dispatch($board->fresh(), 'item.created', ['item_id' => $item->id]);
    }

    // ── Sauvegarde ────────────────────────────────────────
    public function saveMeeting(): void
    {
        $rules = [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'date'  => ['required', 'date'],
            'attendees' => ['array'],
            'attendees.*' => ['string', 'max:255'],
            'bilan' => ['array'],
            'bilan.*' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['array'],
            'recommendations.*' => ['nullable', 'string', 'max:5000'],
            'actions' => ['array', 'min:1'],
            'actions.*.text' => ['nullable', 'string', 'max:255'],
            'actions.*.assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'actions.*.deadline' => ['nullable', 'date'],
            'actions.*.converted' => ['nullable', 'boolean'],
            'actions.*.item_id' => ['nullable', 'integer', 'exists:items,id'],
        ];

        Validator::make([
            'title' => $this->title,
            'date' => $this->date,
            'attendees' => $this->attendees,
            'bilan' => $this->bilan,
            'recommendations' => $this->recommendations,
            'actions' => $this->actions,
        ], $rules)->validate();

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
            $meeting = Meeting::findOrFail($this->meetingId);
            $this->authorize('update', $meeting);
            $meeting->update($data);
        } else {
            $this->authorize('create', Meeting::class);
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
        $workspace = auth()->user()?->activeWorkspace;
        if (!$workspace) return collect([auth()->user()]);

        return collect([$workspace->owner])
            ->merge($workspace->members)
            ->filter()
            ->unique('id')
            ->sortBy('name');
    }

    public function render()
    {
        return view('livewire.meetings.meeting-editor');
    }
}
