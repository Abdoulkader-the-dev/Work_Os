<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class CommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Comment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:1000'],
            'item_id' => ['required', 'exists:items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Le commentaire ne peut pas être vide.',
            'item_id.required' => 'L\'élément est requis.',
            'item_id.exists' => 'L\'élément n\'existe pas.',
        ];
    }
}
