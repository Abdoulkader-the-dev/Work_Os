<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        if (!$item) {
            return false;
        }

        $workspace = $item->group->board->workspace;
        $user = $this->user();

        // Check if user is owner
        if ($workspace->user_id === $user->id) {
            return true;
        }

        // Check user role in workspace
        $member = $workspace->members()
            ->where('user_id', $user->id)
            ->first();

        return $member && in_array($member->pivot->role, ['member', 'admin']);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:todo,progress,ongoing,blocked,done'],
            'priority' => ['nullable', 'in:basse,moyenne,haute,critique'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'deliverable' => ['nullable', 'string'],
            'obstacles' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:users,id'],
            'redirect_view' => ['nullable', 'in:table,kanban,calendar'],
            'redirect_month' => ['nullable', 'integer', 'between:1,12'],
            'redirect_year' => ['nullable', 'integer', 'between:2000,2100'],
        ];
    }
}
