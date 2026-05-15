<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class BoardUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Board|null $board */
        $board = $this->route('board');

        return $board && ($this->user()?->can('update', $board) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
