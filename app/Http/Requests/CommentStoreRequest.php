<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->user()?->activeWorkspace;

        return $workspace && $workspace->members()
            ->where('user_id', $this->user()->id)
            ->whereIn('role', ['member', 'admin'])
            ->exists();
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
