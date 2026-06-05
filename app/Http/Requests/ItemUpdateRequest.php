<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item && ($this->user()?->can('update', $item) ?? false);
    }

    public function rules(): array
    {
        $item = $this->route('item');
        $board = $item?->group?->board;
        $workspaceId = $board?->workspace_id;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(Item::allowedStatuses())],
            'priority' => ['nullable', Rule::in(Item::allowedPriorities())],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'deliverable' => ['nullable', 'string'],
            'obstacles' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'group_id' => [
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->where('board_id', $board?->id)),
            ],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => [
                Rule::exists('workspace_user', 'user_id')->where(fn ($query) => $query->where('workspace_id', $workspaceId)),
            ],
            'redirect_view' => ['nullable', 'in:table,kanban,calendar'],
            'redirect_month' => ['nullable', 'integer', 'between:1,12'],
            'redirect_year' => ['nullable', 'integer', 'between:2000,2100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $item = $this->route('item');
            $status = $this->input('status');

            if ($item && $status && !Item::canTransitionStatus($item->status, $status)) {
                $validator->errors()->add('status', 'La transition de statut demandée est invalide.');
            }
        });
    }
}
