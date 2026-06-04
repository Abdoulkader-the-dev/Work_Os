<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingStoreRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'attendees' => ['nullable', 'array'],
            'attendees.*' => ['string', 'max:255'],
            'bilan' => ['nullable', 'array'],
            'bilan.*' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'array'],
            'recommendations.*' => ['nullable', 'string', 'max:5000'],
            'actions' => ['nullable', 'array'],
            'actions.*.text' => ['nullable', 'string', 'max:255'],
            'actions.*.assignee_id' => ['nullable', 'exists:users,id'],
            'actions.*.deadline' => ['nullable', 'date'],
        ];
    }
}
