<?php

namespace App\Http\Requests;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class WorkspaceMemberUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can('manageMembers', $workspace) ?? false);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:admin,member,reader'],
        ];
    }
}
