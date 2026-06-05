<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemDestroyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item && ($this->user()?->can('delete', $item) ?? false);
    }

    public function rules(): array
    {
        return []; // No validation needed for delete
    }
}
