<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemDestroyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins or owners can delete items
        $item = $this->route('item');

        if (!$item) {
            return false;
        }

        $workspace = $item->group->board->workspace;

        // Check if user is admin of the workspace
        return $workspace->members()
            ->where('user_id', $this->user()->id)
            ->where('role', 'admin')
            ->exists();
    }

    public function rules(): array
    {
        return []; // No validation needed for delete
    }
}
