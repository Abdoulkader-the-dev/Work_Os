<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $board = $this->route('board');

        return $board && ($this->user()?->can('update', $board) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'status' => ['nullable', 'in:todo,progress,ongoing,blocked,done'],
            'priority' => ['nullable', 'in:basse,moyenne,haute,critique'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'deliverable' => ['nullable', 'string'],
            'obstacles' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:users,id'],
            'redirect_view' => ['nullable', 'in:table,kanban,calendar'],
            'redirect_month' => ['nullable', 'integer', 'between:1,12'],
            'redirect_year' => ['nullable', 'integer', 'between:2000,2100'],
        ];
    }
}
