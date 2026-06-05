<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $board = $this->route('board');

        return $board && ($this->user()?->can('update', $board) ?? false);
    }

    public function rules(): array
    {
        $board = $this->route('board');
        $workspaceId = $board?->workspace_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'group_id' => [
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->where('board_id', $board?->id)),
            ],
            'status' => ['nullable', Rule::in(Item::allowedStatuses())],
            'priority' => ['nullable', Rule::in(Item::allowedPriorities())],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'deliverable' => ['nullable', 'string'],
            'obstacles' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => [
                Rule::exists('workspace_user', 'user_id')->where(fn ($query) => $query->where('workspace_id', $workspaceId)),
            ],
            'redirect_view' => ['nullable', 'in:table,kanban,calendar'],
            'redirect_month' => ['nullable', 'integer', 'between:1,12'],
            'redirect_year' => ['nullable', 'integer', 'between:2000,2100'],
        ];
    }
}
