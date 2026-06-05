<?php

namespace App\Http\Requests;

use App\Models\Meeting;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class MeetingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Meeting::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'attendees' => ['nullable', 'array'],
            'attendees.*' => ['string', 'max:255'],
            'bilan' => ['nullable', 'array'],
            'bilan.*' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'array'],
            'recommendations.*' => ['nullable', 'string', 'max:5000'],
            'actions' => ['nullable', 'array'],
            'actions.*.text' => ['nullable', 'string', 'max:255'],
            'actions.*.assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'actions.*.deadline' => ['nullable', 'date'],
            'actions.*.converted' => ['nullable', 'boolean'],
            'actions.*.item_id' => ['nullable', 'integer', 'exists:items,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $workspace = $this->user()?->activeWorkspace;

        if (!$workspace) {
            return;
        }

        $validator->after(function ($validator) use ($workspace) {
            foreach ((array) $this->input('actions', []) as $index => $action) {
                $assigneeId = $action['assignee_id'] ?? null;
                if ($assigneeId) {
                    $assignee = User::find($assigneeId);

                    if (!$assignee || !$assignee->belongsToWorkspace($workspace)) {
                        $validator->errors()->add("actions.$index.assignee_id", 'L\'assigné doit appartenir au workspace.');
                    }
                }

                $itemId = $action['item_id'] ?? null;
                if ($itemId) {
                    $validItem = Item::query()
                        ->whereKey($itemId)
                        ->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace->id))
                        ->exists();

                    if (!$validItem) {
                        $validator->errors()->add("actions.$index.item_id", 'La tâche liée doit appartenir au workspace.');
                    }
                }
            }
        });
    }
}
