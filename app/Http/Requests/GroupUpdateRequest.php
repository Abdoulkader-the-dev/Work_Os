<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Get group from route and check if user can update its board
        $group = $this->route('group');

        return $group && ($this->user()?->can('update', $group->board) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
