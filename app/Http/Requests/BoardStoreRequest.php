<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class BoardStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Board::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
