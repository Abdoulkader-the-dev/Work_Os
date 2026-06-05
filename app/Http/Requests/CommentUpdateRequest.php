<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $comment = $this->route('comment');

        return $comment && ($this->user()?->can('update', $comment) ?? false);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Le commentaire ne peut pas être vide.',
        ];
    }
}
